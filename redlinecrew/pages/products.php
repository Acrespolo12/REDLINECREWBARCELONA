<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Products Page
// ═══════════════════════════════════════════

$db = Database::getInstance();
$pageTitle = 'Productos';

// Filters
$cat       = preg_replace('/[^a-z_]/', '', strtolower($_GET['cat'] ?? ''));
$sort      = in_array($_GET['sort'] ?? '', ['price_asc','price_desc','newest','popular']) ? $_GET['sort'] : 'newest';
$condition = in_array($_GET['condition'] ?? '', ['nuevo','como_nuevo','bueno','aceptable']) ? $_GET['condition'] : '';
$maxPrice  = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$minPrice  = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$page_num  = max(1, (int)($_GET['p'] ?? 1));
$perPage   = 16;
$offset    = ($page_num - 1) * $perPage;

// Build query
$where = ["p.status = 'active'"];
$params = [];

if ($cat && array_key_exists($cat, PRODUCT_CATEGORIES)) {
    $where[] = 'p.category = ?';
    $params[] = $cat;
}
if ($condition) {
    $where[] = 'p.condition_type = ?';
    $params[] = $condition;
}
if ($minPrice > 0) { $where[] = 'p.price >= ?'; $params[] = $minPrice; }
if ($maxPrice > 0) { $where[] = 'p.price <= ?'; $params[] = $maxPrice; }

$whereStr = 'WHERE ' . implode(' AND ', $where);
$orderBy = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular'    => 'p.views DESC',
    default      => 'p.featured DESC, p.created_at DESC',
};

$total = (int)$db->fetch("SELECT COUNT(*) as c FROM products p $whereStr", $params)['c'];
$totalPages = ceil($total / $perPage);

$products = $db->fetchAll(
    "SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset",
    $params
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <!-- Page header -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;">
        <?php if ($cat && isset(PRODUCT_CATEGORIES[$cat])): ?>
          <?= PRODUCT_CATEGORIES[$cat]['icon'] ?> <?= Security::e(PRODUCT_CATEGORIES[$cat]['name']) ?>
        <?php else: ?>
          Todos los Productos
        <?php endif; ?>
      </h1>
      <p style="color:var(--text-muted);margin-top:4px;"><?= number_format($total) ?> anuncios encontrados</p>
    </div>
    <a href="?page=sell" class="btn btn-red"><i class="fas fa-plus"></i> Publicar anuncio</a>
  </div>

  <div style="display:grid;grid-template-columns:240px 1fr;gap:28px;">
    <!-- SIDEBAR FILTERS -->
    <div class="sidebar">
      <div class="sidebar-block">
        <h4><i class="fas fa-filter" style="color:var(--red);"></i> Filtros</h4>

        <form method="get" action="">
          <input type="hidden" name="page" value="products">

          <!-- Category -->
          <div class="form-group">
            <label>Categoría</label>
            <select name="cat" class="form-control" onchange="this.form.submit()">
              <option value="">Todas las categorías</option>
              <?php foreach (PRODUCT_CATEGORIES as $slug => $c): ?>
              <option value="<?= $slug ?>" <?= $cat === $slug ? 'selected' : '' ?>><?= $c['icon'] ?> <?= Security::e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Condition -->
          <div class="form-group">
            <label>Estado</label>
            <select name="condition" class="form-control">
              <option value="">Cualquier estado</option>
              <option value="nuevo" <?= $condition==='nuevo'?'selected':'' ?>>Nuevo</option>
              <option value="como_nuevo" <?= $condition==='como_nuevo'?'selected':'' ?>>Como nuevo</option>
              <option value="bueno" <?= $condition==='bueno'?'selected':'' ?>>Buen estado</option>
              <option value="aceptable" <?= $condition==='aceptable'?'selected':'' ?>>Aceptable</option>
            </select>
          </div>

          <!-- Price range -->
          <div class="form-group">
            <label>Precio (€)</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <input type="number" name="min_price" class="form-control" placeholder="Mín" value="<?= $minPrice ?: '' ?>" min="0">
              <input type="number" name="max_price" class="form-control" placeholder="Máx" value="<?= $maxPrice ?: '' ?>" min="0">
            </div>
          </div>

          <!-- Sort -->
          <div class="form-group">
            <label>Ordenar por</label>
            <select name="sort" class="form-control">
              <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Más recientes</option>
              <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Precio: menor a mayor</option>
              <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Precio: mayor a menor</option>
              <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Más vistos</option>
            </select>
          </div>

          <button type="submit" class="btn btn-red" style="width:100%;">Aplicar filtros</button>
          <a href="?page=products" style="display:block;text-align:center;margin-top:8px;font-size:13px;color:var(--text-muted);">Limpiar filtros</a>
        </form>
      </div>

      <!-- Categories quick links -->
      <div class="sidebar-block">
        <h4><i class="fas fa-th"></i> Categorías</h4>
        <?php foreach (PRODUCT_CATEGORIES as $slug => $c): 
          $cnt = $db->fetch("SELECT COUNT(*) as n FROM products WHERE status='active' AND category=?",[$slug])['n'] ?? 0;
        ?>
        <a href="?page=products&cat=<?= $slug ?>" style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;color:var(--text-dim);font-size:14px;border-bottom:1px solid var(--border);">
          <span><?= $c['icon'] ?> <?= Security::e($c['name']) ?></span>
          <span style="background:var(--bg3);padding:2px 8px;border-radius:10px;font-size:12px;"><?= $cnt ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- PRODUCTS GRID -->
    <div>
      <?php if (empty($products)): ?>
      <div style="text-align:center;padding:80px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
        <div style="font-size:56px;margin-bottom:16px;">🔍</div>
        <h3 style="font-family:var(--font-head);font-size:24px;margin-bottom:8px;">Sin resultados</h3>
        <p style="color:var(--text-muted);">Prueba con otros filtros o <a href="?page=sell">publica el primero</a>.</p>
      </div>
      <?php else: ?>
      <div class="grid-4" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
        <?php foreach ($products as $p):
          $images = json_decode($p['images'] ?? '[]', true);
          $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
          $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
          $isFav = Auth::isLoggedIn() ? (bool)$db->fetch("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?",[$_SESSION['user_id'],$p['id']]) : false;
        ?>
        <div class="product-card fade-in">
          <a href="?page=product&id=<?= $p['id'] ?>">
            <div class="img-wrap">
              <?php if ($img): ?>
              <img data-src="<?= Security::e($img) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="<?= Security::e($p['title']) ?>">
              <?php else: ?>
              <div class="img-placeholder"><?= $catInfo['icon'] ?></div>
              <?php endif; ?>
              <?php if ($p['featured']): ?>
              <div style="position:absolute;top:8px;left:8px;background:var(--gold);color:#000;font-size:10px;font-weight:700;padding:3px 8px;border-radius:4px;font-family:var(--font-head);letter-spacing:1px;">DESTACADO</div>
              <?php endif; ?>
            </div>
          </a>
          <div class="card-body">
            <div class="card-cat"><?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?></div>
            <h3><a href="?page=product&id=<?= $p['id'] ?>" style="color:inherit;"><?= Security::e($p['title']) ?></a></h3>
            <?= conditionLabel($p['condition_type']) ?>
            <div class="card-price"><?= formatPrice($p['price']) ?></div>
            <div class="card-meta">
              <span title="Ubicación"><i class="fas fa-map-marker-alt"></i> <?= Security::e(mb_substr($p['location'] ?: 'España', 0, 15)) ?></span>
              <span><i class="fas fa-eye"></i> <?= $p['views'] ?></span>
              <button class="fav-btn <?= $isFav?'active':'' ?>" data-id="<?= $p['id'] ?>"><?= $isFav?'♥':'♡' ?></button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = max(1,$page_num-2); $i <= min($totalPages,$page_num+2); $i++): 
          $url = "?page=products&cat=$cat&sort=$sort&condition=$condition&min_price=$minPrice&max_price=$maxPrice&p=$i";
        ?>
        <a href="<?= $url ?>" class="page-btn <?= $i==$page_num?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
