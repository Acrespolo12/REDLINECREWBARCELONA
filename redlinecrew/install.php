<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Auto-Installer
//  Accede a: http://tudominio.com/redlinecrew/install.php
//  ¡ELIMINA este archivo tras la instalación!
// ═══════════════════════════════════════════

// Block if already installed
if (file_exists(__DIR__ . '/.installed')) {
    die('<h2 style="font-family:sans-serif;color:#e8192c;">Ya instalado. Elimina install.php del servidor.</h2>');
}

$step   = (int)($_POST['step'] ?? 0);
$errors = [];
$success = false;

if ($step === 1) {
    $host   = trim($_POST['db_host'] ?? 'localhost');
    $user   = trim($_POST['db_user'] ?? '');
    $pass   = $_POST['db_pass'] ?? '';
    $dbname = trim($_POST['db_name'] ?? 'redlinecrew');
    $siteUrl = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass  = $_POST['admin_pass'] ?? '';

    if (!$host || !$user || !$dbname) $errors[] = 'Rellena todos los campos de la base de datos.';
    if (!$adminEmail || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email de admin inválido.';
    if (strlen($adminPass) < 8) $errors[] = 'La contraseña de admin debe tener al menos 8 caracteres.';

    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");

            // Execute SQL schema
            $sql = file_get_contents(__DIR__ . '/install.sql');
            // Remove the CREATE DATABASE / USE lines since we already did it
            $sql = preg_replace('/^CREATE DATABASE.*?;$/m', '', $sql);
            $sql = preg_replace('/^USE.*?;$/m', '', $sql);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $query) {
                if ($query) $pdo->exec($query);
            }

            // Update admin credentials
            $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("UPDATE users SET email=?, password=? WHERE username='admin'")->execute([$adminEmail, $hash]);

            // Write config
            $configContent = file_get_contents(__DIR__ . '/includes/config.php');
            $configContent = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/", "define('DB_HOST', '$host')", $configContent);
            $configContent = preg_replace("/define\('DB_USER',\s*'[^']*'\)/", "define('DB_USER', '$user')", $configContent);
            $configContent = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/", "define('DB_PASS', '$pass')", $configContent);
            $configContent = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/", "define('DB_NAME', '$dbname')", $configContent);
            $configContent = preg_replace("/define\('SITE_URL',\s*'[^']*'\)/", "define('SITE_URL', '$siteUrl')", $configContent);
            file_put_contents(__DIR__ . '/includes/config.php', $configContent);

            // Create upload dirs
            foreach (['uploads/products', 'uploads/avatars', 'logs'] as $dir) {
                if (!is_dir(__DIR__ . "/$dir")) mkdir(__DIR__ . "/$dir", 0755, true);
            }

            // Mark as installed
            file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
            $success = true;

        } catch (PDOException $e) {
            $errors[] = 'Error de base de datos: ' . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>REDLINECREW — Instalador</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Barlow',sans-serif;background:#0a0a0c;color:#e8e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.box{background:#16161e;border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:40px;width:100%;max-width:540px;}
h1{font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:900;text-transform:uppercase;margin-bottom:6px;}
h1 span{color:#e8192c;}
p{color:#6b6b80;font-size:14px;line-height:1.6;}
label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9999aa;margin:18px 0 6px;}
input{width:100%;background:#1a1a22;border:1px solid rgba(255,255,255,.08);border-radius:6px;padding:11px 14px;color:#e8e8f0;font-size:15px;outline:none;transition:.2s;}
input:focus{border-color:#e8192c;box-shadow:0 0 0 3px rgba(232,25,44,.2);}
.btn{display:inline-block;width:100%;background:#e8192c;color:#fff;border:none;border-radius:6px;padding:13px;font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;cursor:pointer;margin-top:24px;transition:.2s;}
.btn:hover{background:#b01020;}
.error{background:rgba(232,25,44,.1);border:1px solid rgba(232,25,44,.4);border-radius:8px;padding:12px 16px;color:#e8192c;font-size:14px;margin-bottom:16px;}
.success{background:rgba(0,214,143,.1);border:1px solid rgba(0,214,143,.3);border-radius:8px;padding:20px;color:#00d68f;font-size:15px;text-align:center;}
.success a{color:#00d68f;font-weight:700;}
.step-badge{background:rgba(232,25,44,.15);color:#e8192c;font-size:12px;padding:4px 12px;border-radius:20px;display:inline-block;margin-bottom:20px;font-weight:700;}
hr{border:none;border-top:1px solid rgba(255,255,255,.06);margin:24px 0;}
small{color:#6b6b80;font-size:12px;display:block;margin-top:4px;}
</style>
</head>
<body>
<div class="box">
  <div class="step-badge">Instalador v1.0</div>
  <h1>RED<span>LINE</span>CREW</h1>
  <p style="margin-bottom:28px;">Configura tu marketplace motero en segundos.</p>

  <?php if ($success): ?>
  <div class="success">
    <div style="font-size:40px;margin-bottom:12px;">🎉</div>
    <strong style="font-family:'Barlow Condensed',sans-serif;font-size:22px;display:block;margin-bottom:8px;">¡INSTALACIÓN COMPLETA!</strong>
    <p style="color:#00d68f;margin-bottom:16px;">REDLINECREW está listo para usar.</p>
    <p style="font-size:13px;color:#6b6b80;margin-bottom:20px;">⚠️ <strong style="color:#f5a623;">IMPORTANTE:</strong> Elimina el archivo <code>install.php</code> de tu servidor ahora.</p>
    <a href="index.php">→ Ir al sitio</a> &nbsp;|&nbsp; <a href="admin/">→ Panel Admin</a>
  </div>

  <?php else: ?>
  <?php foreach ($errors as $e): ?>
  <div class="error"><strong>Error:</strong> <?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <input type="hidden" name="step" value="1">

    <hr>
    <p style="font-size:13px;color:#9999aa;font-weight:600;text-transform:uppercase;letter-spacing:1px;">🗄️ Base de Datos</p>

    <label>Host MySQL</label>
    <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" placeholder="localhost" required>

    <label>Usuario MySQL</label>
    <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" placeholder="root" required>

    <label>Contraseña MySQL</label>
    <input type="password" name="db_pass" placeholder="(en blanco si no tiene)">

    <label>Nombre de la base de datos</label>
    <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'redlinecrew') ?>" placeholder="redlinecrew" required>
    <small>Se creará automáticamente si no existe.</small>

    <hr>
    <p style="font-size:13px;color:#9999aa;font-weight:600;text-transform:uppercase;letter-spacing:1px;">🌐 Sitio Web</p>

    <label>URL del sitio (sin barra final)</label>
    <input type="text" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? 'http://localhost/redlinecrew') ?>" placeholder="https://tudominio.com/redlinecrew" required>

    <hr>
    <p style="font-size:13px;color:#9999aa;font-weight:600;text-transform:uppercase;letter-spacing:1px;">🔑 Cuenta de Administrador</p>

    <label>Email del administrador</label>
    <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" placeholder="admin@tudominio.com" required>

    <label>Contraseña del administrador</label>
    <input type="password" name="admin_pass" placeholder="Mínimo 8 caracteres" required minlength="8">
    <small>Elige una contraseña segura. No la pierdas.</small>

    <button type="submit" class="btn">🚀 Instalar REDLINECREW</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
