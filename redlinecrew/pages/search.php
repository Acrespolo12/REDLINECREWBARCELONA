<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Search Results Page
// ═══════════════════════════════════════════

$db = Database::getInstance();
$q  = trim($_GET['q'] ?? '');
$pageTitle = $q ? 'Búsqueda: ' . Security::e($q) : 'Buscar';

$results = [];
$total   = 0;
$page_n  = max(1,(int)($_GET['p'] ?? 1));
$perPage = 16;
$offset  = ($page_n-1)*$perPage;

if (strlen($q) >= 2) {
    $total = (int)$db->fetch(
        "SELECT COUNT(*) as c FROM products WHERE status='active' AND (MATCH(title,description) AGAINST(? IN BOOLEAN MODE) OR title LIKE ?)",
        [$q.'*', '%'.$q.'%']
    )['c'];
    $results = $db->fetchAll(
        "SELECT p.*, u.username,
         MATCH(p.title,p.description) AGAINST(? IN BOOLEAN MODE) AS relevance
         FROM products p JOIN users u ON p.user_id=u.id
         WHERE p.status='active' AND (MATCH(p.title,p.description) AGAINST(? IN BOOLEAN MODE) OR p.title LIKE ?)
         ORDER BY relevance DESC, p.created_at DESC
         LIMIT $perPage OFFSET $offset",
        [$q, $q.'*', '%'.$q.'%']
    );

    // Also search offers
    $offerResults = $db->fetchAll(
        "SELECT * FROM offers WHERE MATCH(title,description) AGAINST(? IN BOOLEAN MODE) OR title LIKE ? ORDER BY pub_date DESC LIMIT 4",
        [$q.'*', '%'.$q.'%']
    );
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <!-- Search bar -->
  <div style="max-width:600px;margin:0 auto 32px;">
    <form method="GET" action="" style="display:flex;gap:0;">
      <input type="hidden" name="page" value="search">
      <input type="text" name="q" value="<?= Security::e($q) ?>"
             placeholder="Buscar motos, cascos, chaquetas..."
             style="flex:1;background:var(--bg3);border:1px solid var(--border);border-right:none;border-radius:var(--radius) 0 0 var(--radius);padding:14px 18px;color:var(--text);font-size:16px;outline:none;">
      <button type="submit" class="btn btn-red" style="border-radius:0 var(--radius) var(--radius) 0;padding:14px 24px;">
        <i class="fas fa-search"></i> Buscar
      </button>
    </form>
  </div>

  <?php if (!$q): ?>
  <!-- Search suggestions -->
  <div style="text-align:center;padding:40px;">
    <div style="font-size:56px;margin-bottom:16px;">🔍</div>
    <h2 style="font-family:var(--font-head);font-size:28px;margin-bottom:20px;">¿Qué estás buscando?</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">
      <?php foreach (['Casco Shoei','Chaqueta Alpinestars','Honda CB650R','Guantes invierno','Botas TCX','Escape Akrapovic','Neumáticos Michelin'] as $sug): ?>
      <a href="?page=search&q=<?= urlencode($sug) ?>" style="background:var(--bg3);border:1px solid var(--border);padding:8px 18px;border-radius:20px;color:var(--text-dim);font-size:14px;transition:.2s;" onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
        <?= $sug ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php elseif (empty($results) && empty($offerResults ?? [])): ?>
  <div style="text-align:center;padding:60px;">
    <div style="font-size:56px;margin-bottom:16px;">😕</div>
    <h2 style="font-family:var(--font-head);font-size:28px;margin-bottom:8px;">Sin resultados para "<?= Security::e($q) ?>"</h2>
    <p style="color:var(--text-muted);">Prueba con otras palabras clave o <a href="?page=products">explora todos los productos</a>.</p>
  </div>

  <?php else: ?>
  <!-- Results header -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="font-family:var(--font-head);font-size:24px;font-weight:700;">
      <?= number_format($total) ?> resultado<?= $total!=1?'s':'' ?> para "<span style="color:var(--red)"><?= Security::e($q) ?></span>"
    </h1>
  </div>

  <!-- Offers in search -->
  <?php if (!empty($offerResults)): ?>
  <div style="background:rgba(245,166,35,.07);border:1px solid rgba(245,166,35,.2);border-radius:var(--radius-lg);padding:20px;margin-bottom:32px;">
    <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;color:var(--gold);margin-bottom:14px;">
      ⚡ Ofertas relacionadas en webs especializadas
    </h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
      <?php foreach ($offerResults as $o): ?>
      <a href="<?= Security::e($o['url']) ?>" target="_blank" rel="noopener" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:12px;display:flex;gap:12px;align-items:center;color:var(--text);">
        <?php if ($o['image']): ?>
        <img src="<?= Security::e($o['image']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;flex-shrink:0;">
        <?php endif; ?>
        <div>
          <div style="font-size:10px;color:var(--gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;"><?= Security::e($o['source']) ?></div>
          <div style="font-size:13px;font-weight:600;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= Security::e($o['title']) ?></div>
          <?php if ($o['price']): ?><div style="color:var(--gold);font-weight:700;margin-top:4px;"><?= Security::e($o['price']) ?> €</div><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Product results grid -->
  <?php if (!empty($results)): ?>
  <div class="grid-4" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
    <?php foreach ($results as $p):
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
          <span><?= timeAgo($p['created_at']) ?></span>
          <button class="fav-btn" data-id="<?= $p['id'] ?>">♡</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (ceil($total/$perPage) > 1): ?>
  <div class="pagination">
    <?php for($i=max(1,$page_n-2);$i<=min(ceil($total/$perPage),$page_n+2);$i++): ?>
    <a href="?page=search&q=<?= urlencode($q) ?>&p=<?= $i ?>" class="page-btn <?= $i==$page_n?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
