<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Contact Page
// ═══════════════════════════════════════════
$pageTitle = 'Contacto';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:48px;padding-bottom:80px;max-width:900px;">
  <div style="text-align:center;margin-bottom:48px;">
    <h1 style="font-family:var(--font-head);font-size:48px;font-weight:900;text-transform:uppercase;">
      Contacta con <span style="color:var(--red)">Nosotros</span>
    </h1>
    <p style="color:var(--text-muted);font-size:16px;max-width:500px;margin:10px auto 0;">¿Tienes alguna duda, sugerencia o problema? Estamos aquí para ayudarte.</p>
  </div>

  <div class="grid-2" style="gap:40px;align-items:start;">
    <!-- Contact form -->
    <div class="card" style="padding:32px;">
      <h2 style="font-family:var(--font-head);font-size:22px;font-weight:700;margin-bottom:24px;text-transform:uppercase;">
        <i class="fas fa-paper-plane" style="color:var(--red);"></i> Enviar mensaje
      </h2>
      <form method="POST" action="">
        <input type="hidden" name="form_action" value="contact">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <div class="form-group">
          <label>Nombre completo *</label>
          <input type="text" name="name" class="form-control" placeholder="Tu nombre" required
                 value="<?= Security::e($_SESSION['user_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" class="form-control" placeholder="tu@email.com" required>
        </div>
        <div class="form-group">
          <label>Asunto</label>
          <select name="subject" class="form-control">
            <option>Consulta general</option>
            <option>Problema con un anuncio</option>
            <option>Reporte de usuario</option>
            <option>Sugerencia</option>
            <option>Problema técnico</option>
            <option>Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Mensaje *</label>
          <textarea name="message" class="form-control" rows="5" placeholder="Describe tu consulta con detalle..." required data-maxlength="1000"></textarea>
        </div>
        <button type="submit" class="btn btn-red" style="width:100%;">
          <i class="fas fa-paper-plane"></i> Enviar mensaje
        </button>
      </form>
    </div>

    <!-- Contact info + FAQ -->
    <div>
      <!-- Contact channels -->
      <div class="card" style="padding:24px;margin-bottom:20px;">
        <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:18px;text-transform:uppercase;">
          <i class="fas fa-headset" style="color:var(--red);"></i> Otros canales
        </h3>
        <?php foreach ([
          ['fas fa-envelope','Email','info@redlinecrew.com','mailto:info@redlinecrew.com','var(--red)'],
          ['fab fa-instagram','Instagram','@redlinecrew','https://instagram.com/redlinecrew','#e1306c'],
          ['fab fa-whatsapp','WhatsApp','+34 600 000 000','https://wa.me/34600000000','#25d366'],
          ['fab fa-youtube','YouTube','REDLINECREW','https://youtube.com','#ff0000'],
        ] as [$icon,$label,$value,$href,$color]): ?>
        <a href="<?= $href ?>" target="_blank" style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border);color:var(--text);">
          <div style="width:36px;height:36px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;color:<?= $color ?>;">
            <i class="<?= $icon ?>"></i>
          </div>
          <div>
            <div style="font-size:12px;color:var(--text-muted);"><?= $label ?></div>
            <div style="font-size:14px;font-weight:600;"><?= $value ?></div>
          </div>
          <i class="fas fa-external-link-alt" style="margin-left:auto;font-size:12px;color:var(--text-muted);"></i>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- FAQ -->
      <div class="card" style="padding:24px;">
        <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:18px;text-transform:uppercase;">
          <i class="fas fa-question-circle" style="color:var(--red);"></i> FAQ
        </h3>
        <?php foreach ([
          ['¿Cómo publico un anuncio?', 'Regístrate, ve a "Vender" y rellena el formulario. Se revisará en menos de 24h.'],
          ['¿Cuánto cuesta publicar?', 'Publicar es completamente gratuito para todos los usuarios.'],
          ['¿Cómo contacto con un vendedor?', 'Directamente por WhatsApp, teléfono o email desde la ficha del producto.'],
          ['¿Puedo eliminar mi anuncio?', 'Sí, desde tu perfil puedes editar o eliminar cualquier anuncio.'],
          ['¿Las ofertas son de REDLINECREW?', 'No, las ofertas son de webs externas especializadas, actualizadas automáticamente.'],
        ] as [$q,$a]): ?>
        <details style="border-bottom:1px solid var(--border);padding:12px 0;">
          <summary style="cursor:pointer;font-weight:600;font-size:14px;list-style:none;display:flex;align-items:center;justify-content:space-between;">
            <?= $q ?> <span style="color:var(--red);">+</span>
          </summary>
          <p style="color:var(--text-muted);font-size:13px;margin-top:8px;line-height:1.6;"><?= $a ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
