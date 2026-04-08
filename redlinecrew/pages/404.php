<?php
// ═══════════════════════════════════════════
//  REDLINECREW - 404 Page
// ═══════════════════════════════════════════
$pageTitle = 'Página no encontrada';
require_once __DIR__ . '/../includes/header.php';
?>
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:40px;">
  <div>
    <div style="font-family:var(--font-head);font-size:120px;font-weight:900;color:var(--red);line-height:1;opacity:.3;">404</div>
    <h1 style="font-family:var(--font-head);font-size:36px;font-weight:900;margin-bottom:12px;text-transform:uppercase;">Página no encontrada</h1>
    <p style="color:var(--text-muted);margin-bottom:28px;">La ruta que buscas no existe o fue movida.</p>
    <div style="display:flex;gap:12px;justify-content:center;">
      <a href="<?= SITE_URL ?>" class="btn btn-red"><i class="fas fa-home"></i> Inicio</a>
      <a href="?page=products" class="btn btn-ghost">Ver productos</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
