<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Favorites Page
// ═══════════════════════════════════════════
Auth::requireLogin();
$db = Database::getInstance();
$pageTitle = 'Mis Favoritos';
$uid = (int)$_SESSION['user_id'];

$favorites = $db->fetchAll(
    "SELECT p.*, u.username FROM products p
     JOIN users u ON p.user_id = u.id
     JOIN favorites f ON f.product_id = p.id
     WHERE f.user_id = ? AND p.status = 'active'
     ORDER BY f.created_at DESC",
    [$uid]
);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;margin-bottom:24px;">
    <span style="color:var(--red)">♥</span> Mis Favoritos
    <span style="font-size:16px;color:var(--text-muted);font-weight:400;">(<?= count($favorites) ?>)</span>
  </h1>

  <?php if (empty($favorites)): ?>
  <div style="text-align:center;padding:80px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:16px;">♡</div>
    <h3 style="font-family:var(--font-head);font-size:24px;margin-bottom:8px;">Sin favoritos todavía</h3>
    <p style="color:var(--text-muted);margin-bottom:24px;">Guarda productos que te interesen pulsando el ♡ en cualquier anuncio.</p>
    <a href="?page=products" class="btn btn-red">Explorar productos</a>
  </div>
  <?php else: ?>
  <div class="grid-4" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
    <?php foreach ($favorites as $p):
      $images = json_decode($p['images'] ?? '[]', true);
      $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
      $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
    ?>
    <div class="product-card fade-in">
      <a href="?page=product&id=<?= $p['id'] ?>">
        <div class="img-wrap">
          <?php if ($img): ?><img data-src="<?= Security::e($img) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="<?= Security::e($p['title']) ?>">
          <?php else: ?><div class="img-placeholder"><?= $catInfo['icon'] ?></div><?php endif; ?>
        </div>
      </a>
      <div class="card-body">
        <div class="card-cat"><?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?></div>
        <h3><a href="?page=product&id=<?= $p['id'] ?>" style="color:inherit;"><?= Security::e($p['title']) ?></a></h3>
        <?= conditionLabel($p['condition_type']) ?>
        <div class="card-price"><?= formatPrice($p['price']) ?></div>
        <div class="card-meta">
          <span><?= Security::e($p['username']) ?></span>
          <button class="fav-btn active" data-id="<?= $p['id'] ?>">♥</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
