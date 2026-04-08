<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Register Page
// ═══════════════════════════════════════════
if (Auth::isLoggedIn()) redirect('home');
$pageTitle = 'Crear cuenta';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="min-height:calc(100vh - 200px);display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="width:100%;max-width:480px;">
    <div style="text-align:center;margin-bottom:32px;">
      <div style="font-family:var(--font-head);font-size:32px;font-weight:900;margin-bottom:8px;">RED<span style="color:var(--red)">LINE</span>CREW</div>
      <h1 style="font-size:24px;font-weight:600;">Únete a la comunidad</h1>
      <p style="color:var(--text-muted);margin-top:4px;">Crea tu cuenta gratis en segundos</p>
    </div>

    <!-- Benefits -->
    <div style="display:flex;gap:12px;justify-content:center;margin-bottom:28px;flex-wrap:wrap;">
      <?php foreach (['Vende gratis','Guarda favoritos','Mensajes directos'] as $benefit): ?>
      <span style="background:rgba(0,214,143,.1);border:1px solid rgba(0,214,143,.3);color:var(--green);font-size:12px;padding:4px 12px;border-radius:20px;">
        ✓ <?= $benefit ?>
      </span>
      <?php endforeach; ?>
    </div>

    <div class="card" style="padding:32px;">
      <form method="POST" action="" id="register-form">
        <input type="hidden" name="form_action" value="register">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

        <div class="form-group">
          <label><i class="fas fa-user" style="color:var(--red);"></i> Nombre de usuario</label>
          <input type="text" name="username" class="form-control" placeholder="tu_nombre_moto" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guión bajo" autocomplete="username">
          <small style="color:var(--text-muted);">Solo letras, números y _ (3-30 caracteres)</small>
        </div>

        <div class="form-group">
          <label><i class="fas fa-envelope" style="color:var(--red);"></i> Email</label>
          <input type="email" name="email" class="form-control" placeholder="tu@email.com" required autocomplete="email">
        </div>

        <div class="form-group">
          <label><i class="fas fa-lock" style="color:var(--red);"></i> Contraseña</label>
          <div style="position:relative;">
            <input type="password" name="password" id="pass-reg" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8" autocomplete="new-password" style="padding-right:44px;">
            <button type="button" onclick="togglePass('pass-reg')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);">👁️</button>
          </div>
          <!-- Strength indicator -->
          <div id="strength-bar" style="height:3px;background:var(--bg3);border-radius:2px;margin-top:6px;overflow:hidden;">
            <div id="strength-fill" style="height:100%;width:0;transition:.3s;border-radius:2px;"></div>
          </div>
          <small id="strength-label" style="color:var(--text-muted);font-size:12px;"></small>
        </div>

        <div class="form-group">
          <label><i class="fas fa-lock" style="color:var(--red);"></i> Confirmar contraseña</label>
          <input type="password" id="pass-confirm" class="form-control" placeholder="Repite la contraseña" required minlength="8" autocomplete="new-password">
        </div>

        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:20px;">
          <input type="checkbox" id="terms" required style="margin-top:3px;accent-color:var(--red);">
          <label for="terms" style="font-size:13px;color:var(--text-muted);cursor:pointer;">
            Acepto los <a href="?page=terms" style="color:var(--red);">Términos de uso</a> y la <a href="?page=privacy" style="color:var(--red);">Política de privacidad</a>
          </label>
        </div>

        <button type="submit" class="btn btn-red" style="width:100%;">
          <i class="fas fa-rocket"></i> Crear cuenta gratis
        </button>
      </form>

      <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--border);font-size:14px;color:var(--text-muted);">
        ¿Ya tienes cuenta?
        <a href="?page=login" style="color:var(--red);font-weight:600;">Iniciar sesión</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePass(id) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
}

// Password strength
document.getElementById('pass-reg').addEventListener('input', function() {
  const v = this.value;
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const bar = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');
  const colors = ['','#e8192c','#f5a623','#f5a623','#00d68f'];
  const labels = ['','Muy débil','Débil','Media','Fuerte'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score];
  label.textContent = labels[score];
  label.style.color = colors[score];
});

// Password match validation
document.getElementById('register-form').addEventListener('submit', function(e) {
  const p1 = document.getElementById('pass-reg').value;
  const p2 = document.getElementById('pass-confirm').value;
  if (p1 !== p2) {
    e.preventDefault();
    RLC.toast('Las contraseñas no coinciden', 'error');
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
