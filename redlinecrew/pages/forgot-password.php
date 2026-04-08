<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Forgot Password Page
// ═══════════════════════════════════════════

if (Auth::isLoggedIn()) redirect('home');
$pageTitle = 'Recuperar contraseña';
$db = Database::getInstance();
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token inválido');
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $user  = $db->fetch("SELECT id, username FROM users WHERE email = ? AND active = 1", [$email]);
        if ($user) {
            $token = Security::generateToken(32);
            $expires = date('Y-m-d H:i:s', time() + 3600);
            // Store token (reuse rate_limits table for simplicity)
            $db->query("DELETE FROM rate_limits WHERE `key` LIKE 'reset_%'");
            $db->insert('rate_limits', [
                'key'          => 'reset_' . $token,
                'attempts'     => $user['id'],
                'locked_until' => $expires,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            // In production, send email here. For now, show token link.
            $resetLink = SITE_URL . "/?page=reset-password&token=$token";
            // Log for admin
            $db->insert('activity_logs', [
                'user_id'    => $user['id'],
                'action'     => 'password_reset_request',
                'details'    => "Reset requested for $email. Link: $resetLink",
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        // Always show success (don't reveal if email exists)
        $sent = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="min-height:calc(100vh - 200px);display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="width:100%;max-width:420px;">
    <div style="text-align:center;margin-bottom:32px;">
      <div style="font-family:var(--font-head);font-size:32px;font-weight:900;margin-bottom:8px;">RED<span style="color:var(--red)">LINE</span>CREW</div>
      <h1 style="font-size:22px;">Recuperar contraseña</h1>
      <p style="color:var(--text-muted);margin-top:6px;font-size:14px;">Introduce tu email y te enviaremos instrucciones.</p>
    </div>

    <?php if ($sent): ?>
    <div class="alert alert-success" style="text-align:center;padding:24px;">
      <div style="font-size:32px;margin-bottom:10px;">📧</div>
      <strong>¡Enviado!</strong><br>
      Si el email existe en nuestra base de datos recibirás instrucciones. Revisa también el spam.<br>
      <a href="?page=login" style="display:inline-block;margin-top:16px;" class="btn btn-ghost btn-sm">← Volver al login</a>
    </div>
    <?php else: ?>
    <div class="card" style="padding:32px;">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <div class="form-group">
          <label><i class="fas fa-envelope" style="color:var(--red);"></i> Tu email</label>
          <input type="email" name="email" class="form-control" placeholder="tu@email.com" required autocomplete="email">
        </div>
        <button type="submit" class="btn btn-red" style="width:100%;margin-top:8px;">
          <i class="fas fa-paper-plane"></i> Enviar instrucciones
        </button>
      </form>
      <div style="text-align:center;margin-top:20px;font-size:14px;">
        <a href="?page=login" style="color:var(--text-muted);">← Volver al login</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
