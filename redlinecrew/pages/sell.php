<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Sell / Publish Product Page
// ═══════════════════════════════════════════

Auth::requireLogin();
$pageTitle = 'Publicar Anuncio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:860px;">
  <div style="text-align:center;margin-bottom:40px;">
    <h1 style="font-family:var(--font-head);font-size:42px;font-weight:900;text-transform:uppercase;">
      Publica tu <span style="color:var(--red);">Anuncio</span>
    </h1>
    <p style="color:var(--text-muted);">Llega a miles de moteros. Gratis, rápido y fácil.</p>
  </div>

  <!-- Steps indicator -->
  <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:40px;">
    <?php foreach (['Información','Fotos','Contacto','Revisar'] as $i => $step): ?>
    <div style="display:flex;align-items:center;">
      <div style="width:32px;height:32px;border-radius:50%;background:<?= $i===0?'var(--red)':'var(--bg3)' ?>;border:1px solid <?= $i===0?'var(--red)':'var(--border)' ?>;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;font-size:14px;color:<?= $i===0?'#fff':'var(--text-muted)' ?>;">
        <?= $i+1 ?>
      </div>
      <span style="margin-left:8px;font-size:13px;color:<?= $i===0?'var(--text)':'var(--text-muted)' ?>;font-weight:<?= $i===0?600:400 ?>;"><?= $step ?></span>
      <?php if ($i < 3): ?>
      <div style="width:40px;height:1px;background:var(--border);margin:0 12px;"></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <form method="POST" action="" enctype="multipart/form-data" id="sell-form">
    <input type="hidden" name="form_action" value="sell_product">
    <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

    <div class="card" style="padding:32px;">
      <!-- TITLE & CATEGORY -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        <div class="form-group">
          <label>Título del anuncio *</label>
          <input type="text" name="title" class="form-control" placeholder="Ej: Casco Shoei NXR2 talla M, poco uso" required maxlength="200" data-maxlength="200">
        </div>
        <div class="form-group">
          <label>Categoría *</label>
          <select name="category" class="form-control" required>
            <option value="">-- Seleccionar --</option>
            <?php foreach (PRODUCT_CATEGORIES as $slug => $cat): ?>
            <option value="<?= $slug ?>"><?= $cat['icon'] ?> <?= Security::e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- DESCRIPTION -->
      <div class="form-group">
        <label>Descripción detallada *</label>
        <textarea name="description" class="form-control" rows="5" placeholder="Describe el producto con detalle: marca, modelo, tamaño, estado, motivo de venta..." required data-maxlength="2000" style="min-height:130px;"></textarea>
      </div>

      <!-- PRICE & CONDITION -->
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
        <div class="form-group">
          <label>Precio (€) *</label>
          <input type="number" name="price" class="form-control" placeholder="0.00" required min="1" max="999999" step="0.01">
        </div>
        <div class="form-group">
          <label>Estado del producto *</label>
          <select name="condition_type" class="form-control" required>
            <option value="nuevo">✨ Nuevo</option>
            <option value="como_nuevo">🌟 Como nuevo</option>
            <option value="bueno" selected>👍 Buen estado</option>
            <option value="aceptable">👌 Aceptable</option>
          </select>
        </div>
        <div class="form-group">
          <label>Ubicación</label>
          <input type="text" name="location" class="form-control" placeholder="Ciudad o provincia" maxlength="100">
        </div>
      </div>

      <hr class="separator">

      <!-- IMAGE UPLOAD -->
      <div class="form-group">
        <label>Fotos del producto (máx. 6)</label>
        <div class="upload-zone" id="upload-area">
          <div class="upload-icon">📸</div>
          <p><strong>Arrastra las fotos aquí</strong> o haz clic para seleccionar</p>
          <p style="font-size:12px;margin-top:6px;">JPG, PNG, WEBP — Máx. 5MB por foto</p>
          <input type="file" id="product-images" name="images[]" accept="image/*" multiple style="display:none;">
        </div>
        <div id="image-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;"></div>
      </div>

      <hr class="separator">

      <!-- CONTACT INFO -->
      <h3 style="font-family:var(--font-head);font-size:20px;font-weight:700;margin-bottom:16px;text-transform:uppercase;">
        <i class="fas fa-address-card" style="color:var(--red);"></i> Información de contacto
      </h3>
      <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">Al menos uno de los siguientes campos es obligatorio.</p>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
        <div class="form-group">
          <label><i class="fas fa-phone"></i> Teléfono</label>
          <input type="tel" name="contact_phone" class="form-control" placeholder="+34 6XX XXX XXX">
        </div>
        <div class="form-group">
          <label><i class="fas fa-envelope"></i> Email de contacto</label>
          <input type="email" name="contact_email" class="form-control" placeholder="tu@email.com">
        </div>
        <div class="form-group">
          <label><i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
          <input type="tel" name="contact_whatsapp" class="form-control" placeholder="+34 6XX XXX XXX">
        </div>
      </div>

      <!-- Info box -->
      <div class="alert alert-info" style="margin-top:8px;">
        <i class="fas fa-info-circle"></i>
        Tu anuncio será revisado por un moderador antes de publicarse. Recibirás una notificación cuando esté activo (normalmente menos de 24h).
      </div>

      <!-- Submit -->
      <div style="display:flex;gap:16px;justify-content:flex-end;margin-top:24px;">
        <a href="?page=home" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-red btn-lg">
          <i class="fas fa-paper-plane"></i> Publicar Anuncio
        </button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
