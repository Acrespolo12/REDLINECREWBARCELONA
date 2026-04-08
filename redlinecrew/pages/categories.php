<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Categories Page
// ═══════════════════════════════════════════
$db = Database::getInstance();
$pageTitle = 'Categorías';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:40px;padding-bottom:60px;">
  <div style="text-align:center;margin-bottom:40px;">
    <h1 style="font-family:var(--font-head);font-size:48px;font-weight:900;text-transform:uppercase;">
      Explora por <span style="color:var(--red)">Categoría</span>
    </h1>
    <p style="color:var(--text-muted);">Encuentra exactamente lo que buscas</p>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
    <?php foreach (PRODUCT_CATEGORIES as $slug => $cat):
      $count   = $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='active' AND category=?", [$slug])['c'] ?? 0;
      $latest  = $db->fetchAll("SELECT images FROM products WHERE status='active' AND category=? ORDER BY created_at DESC LIMIT 3", [$slug]);
    ?>
    <a href="?page=products&cat=<?= $slug ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-align:center;text-decoration:none;color:var(--text);transition:all .25s;display:flex;flex-direction:column;align-items:center;gap:12px;" onmouseover="this.style.borderColor='var(--red)';this.style.transform='translateY(-3px)';this.style.boxShadow='0 0 20px rgba(232,25,44,.2)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='';this.style.boxShadow=''">
      <div style="font-size:48px;"><?= $cat['icon'] ?></div>
      <div>
        <div style="font-family:var(--font-head);font-size:18px;font-weight:700;text-transform:uppercase;"><?= Security::e($cat['name']) ?></div>
        <div style="font-size:13px;color:<?= $count?'var(--red)':'var(--text-muted)' ?>;margin-top:4px;font-weight:<?= $count?'700':'400' ?>;">
          <?= $count ?> anuncio<?= $count!=1?'s':'' ?>
        </div>
      </div>
      <?php if (!empty($latest)): ?>
      <div style="display:flex;gap:4px;justify-content:center;">
        <?php foreach ($latest as $l):
          $imgs = json_decode($l['images'] ?? '[]', true);
          if (!empty($imgs)): ?>
          <img src="<?= UPLOAD_URL . Security::e($imgs[0]) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;opacity:.7;">
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
