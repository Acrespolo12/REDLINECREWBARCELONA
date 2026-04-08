<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Login Page
// ═══════════════════════════════════════════
if (Auth::isLoggedIn()) redirect('home');
$pageTitle = 'Iniciar sesión';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="min-height:calc(100vh - 200px);display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="width:100%;max-width:420px;">
    <div style="text-align:center;margin-bottom:32px;">
      <div style="font-family:var(--font-head);font-size:32px;font-weight:900;margin-bottom:8px;">RED<span style="color:var(--red)">LINE</span>CREW</div>
      <h1 style="font-size:24px;font-weight:600;">Bienvenido de vuelta</h1>
      <p style="color:var(--text-muted);margin-top:4px;">Inicia sesión en tu cuenta</p>
    </div>

    <div class="card" style="padding:32px;">
      <form method="POST" action="">
        <input type="hidden" name="form_action" value="login">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

        <div class="form-group">
          <label><i class="fas fa-envelope" style="color:var(--red);"></i> Email</label>
          <input type="email" name="email" class="form-control" placeholder="tu@email.com" required autocomplete="email">
        </div>
        <div class="form-group">
          <label>
            <i class="fas fa-lock" style="color:var(--red);"></i> Contraseña
            <a href="?page=forgot-password" style="float:right;font-size:12px;text-transform:none;font-weight:400;">¿Olvidaste la contraseña?</a>
          </label>
          <div style="position:relative;">
            <input type="password" name="password" id="pass-input" class="form-control" placeholder="••••••••" required autocomplete="current-password" style="padding-right:44px;">
            <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);font-size:14px;">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn btn-red" style="width:100%;margin-top:8px;">
          <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </button>
      </form>

      <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--border);font-size:14px;color:var(--text-muted);">
        ¿No tienes cuenta?
        <a href="?page=register" style="color:var(--red);font-weight:600;">Regístrate gratis</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const i = document.getElementById('pass-input');
  i.type = i.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
