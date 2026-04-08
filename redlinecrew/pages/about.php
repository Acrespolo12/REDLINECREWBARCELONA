<?php
// ═══════════════════════════════════════════
//  REDLINECREW - About Page
// ═══════════════════════════════════════════
$pageTitle = 'Sobre nosotros';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:48px;padding-bottom:80px;max-width:800px;">
  <h1 style="font-family:var(--font-head);font-size:48px;font-weight:900;text-transform:uppercase;margin-bottom:8px;">
    Sobre <span style="color:var(--red)">REDLINECREW</span>
  </h1>
  <p style="color:var(--text-muted);font-size:16px;margin-bottom:40px;">La comunidad motera que lo tiene todo</p>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:36px;margin-bottom:24px;">
    <h2 style="font-family:var(--font-head);font-size:24px;font-weight:700;margin-bottom:16px;text-transform:uppercase;display:flex;align-items:center;gap:10px;">
      <span style="color:var(--red)">🏍️</span> Nuestra misión
    </h2>
    <p style="color:var(--text-dim);line-height:1.8;font-size:15px;">
      REDLINECREW nació con una idea simple: crear el mejor marketplace especializado en motos y equipamiento de España. Somos una plataforma donde compradores y vendedores se conectan directamente, sin intermediarios, con total transparencia.
    </p>
    <p style="color:var(--text-dim);line-height:1.8;font-size:15px;margin-top:14px;">
      Además, agregamos automáticamente las mejores ofertas de las webs moteras más importantes para que nunca te pierdas un chollo.
    </p>
  </div>

  <div class="grid-3" style="margin-bottom:32px;">
    <?php foreach ([
      ['🔒','Seguridad total','Cada anuncio es revisado por nuestro equipo antes de publicarse.'],
      ['💬','Comunidad real','Miles de moteros activos comprando y vendiendo cada día.'],
      ['⚡','Ofertas en tiempo real','Agregamos ofertas de más de 6 webs especializadas automáticamente.'],
    ] as [$icon,$title,$desc]): ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-align:center;">
      <div style="font-size:36px;margin-bottom:12px;"><?= $icon ?></div>
      <h3 style="font-family:var(--font-head);font-size:16px;font-weight:700;text-transform:uppercase;margin-bottom:8px;"><?= $title ?></h3>
      <p style="font-size:13px;color:var(--text-muted);line-height:1.6;"><?= $desc ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;padding:40px;background:linear-gradient(135deg,var(--bg3),var(--bg2));border:1px solid var(--border-red);border-radius:var(--radius-lg);">
    <h2 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;margin-bottom:12px;">¿Listo para unirte?</h2>
    <p style="color:var(--text-muted);margin-bottom:20px;">Registro gratuito. Sin comisiones. Sin complicaciones.</p>
    <a href="?page=register" class="btn btn-red btn-lg"><i class="fas fa-rocket"></i> Crear cuenta gratis</a>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
