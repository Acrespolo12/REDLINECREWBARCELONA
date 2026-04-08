<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Home Page
// ═══════════════════════════════════════════

$db = Database::getInstance();
$pageTitle = 'Inicio';
$pageDesc  = 'La comunidad motera definitiva. Compra, vende y descubre las mejores ofertas.';

// Featured products
$featured = $db->fetchAll("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'active' ORDER BY p.featured DESC, p.created_at DESC LIMIT 8");

// Latest offers
$latestOffers = $db->fetchAll("SELECT * FROM offers ORDER BY pub_date DESC LIMIT 6");

// Stats
$stats = [
    'products' => $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c'] ?? 0,
    'users'    => $db->fetch("SELECT COUNT(*) as c FROM users WHERE active=1")['c'] ?? 0,
    'offers'   => $db->fetch("SELECT COUNT(*) as c FROM offers")['c'] ?? 0,
];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────── -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="container">
    <div class="hero-content fade-in">
      <div class="hero-tag">
        <span class="live-dot"></span>
        La comunidad motera número 1
      </div>
      <h1>
        RIDE.<br>
        <span class="red">BUY.</span><br>
        SELL.
      </h1>
      <p>El marketplace definitivo para la comunidad motera. Encuentra equipamiento, repuestos y motos. Vende lo que ya no necesitas.</p>
      <div class="hero-actions">
        <a href="?page=products" class="btn btn-red btn-lg">
          <i class="fas fa-store"></i> Ver Productos
        </a>
        <a href="?page=sell" class="btn btn-outline btn-lg">
          <i class="fas fa-plus"></i> Vender
        </a>
        <a href="?page=offers" class="btn btn-ghost btn-lg" style="border-color:var(--gold);color:var(--gold);">
          <i class="fas fa-bolt"></i> Ofertas
        </a>
      </div>
      <div class="hero-stats">
        <div>
          <div class="hero-stat-num"><?= number_format($stats['products']) ?>+</div>
          <div class="hero-stat-label">Productos activos</div>
        </div>
        <div>
          <div class="hero-stat-num"><?= number_format($stats['users']) ?>+</div>
          <div class="hero-stat-label">Miembros</div>
        </div>
        <div>
          <div class="hero-stat-num"><?= number_format($stats['offers']) ?>+</div>
          <div class="hero-stat-label">Ofertas externas</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CATEGORIES ────────────────────────────── -->
<section class="section-sm" style="background:var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="cat-grid">
      <?php foreach (PRODUCT_CATEGORIES as $slug => $cat): 
        $count = $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='active' AND category=?", [$slug])['c'] ?? 0;
      ?>
      <a href="?page=products&cat=<?= $slug ?>" class="cat-card">
        <span class="cat-icon"><?= $cat['icon'] ?></span>
        <span class="cat-name"><?= Security::e($cat['name']) ?></span>
        <span style="font-size:11px;color:var(--text-muted);"><?= $count ?> anuncios</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FEATURED PRODUCTS ─────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Productos Destacados</h2>
      <a href="?page=products" class="btn btn-ghost btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
    </div>

    <?php if (empty($featured)): ?>
    <div style="text-align:center;padding:60px;color:var(--text-muted);">
      <div style="font-size:48px;margin-bottom:16px;">🏍️</div>
      <p>Aún no hay productos. ¡Sé el primero en vender!</p>
      <a href="?page=sell" class="btn btn-red" style="margin-top:16px;">Publicar anuncio</a>
    </div>
    <?php else: ?>
    <div class="grid-4">
      <?php foreach ($featured as $p):
        $images = json_decode($p['images'] ?? '[]', true);
        $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
        $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
        $isFav = Auth::isLoggedIn() ? (bool)$db->fetch("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?", [$_SESSION['user_id'], $p['id']]) : false;
      ?>
      <div class="product-card fade-in">
        <a href="?page=product&id=<?= $p['id'] ?>">
          <div class="img-wrap">
            <?php if ($img): ?>
            <img data-src="<?= Security::e($img) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="<?= Security::e($p['title']) ?>">
            <?php else: ?>
            <div class="img-placeholder"><?= $catInfo['icon'] ?></div>
            <?php endif; ?>
          </div>
        </a>
        <div class="card-body">
          <div class="card-cat"><?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?></div>
          <h3><a href="?page=product&id=<?= $p['id'] ?>" style="color:inherit;"><?= Security::e($p['title']) ?></a></h3>
          <?= conditionLabel($p['condition_type']) ?>
          <div class="card-price"><?= formatPrice($p['price']) ?></div>
          <div class="card-meta">
            <span><i class="fas fa-map-marker-alt"></i> <?= Security::e($p['location'] ?: 'España') ?></span>
            <span><?= timeAgo($p['created_at']) ?></span>
            <button class="fav-btn <?= $isFav ? 'active' : '' ?>" data-id="<?= $p['id'] ?>" title="Favorito">
              <?= $isFav ? '♥' : '♡' ?>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── OFFERS SECTION ────────────────────────── -->
<section class="section" style="background:var(--bg2);border-top:1px solid var(--border);">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title" style="color:var(--gold);">⚡ Ofertas Moteras</h2>
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:12px;color:var(--text-muted);"><span class="live-dot"></span> Auto-actualizado</span>
        <a href="?page=offers" class="btn btn-ghost btn-sm">Ver todas <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>

    <?php if (empty($latestOffers)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted);">
      <p>Las ofertas se cargarán automáticamente. <a href="?page=offers" style="color:var(--red);">Actualizar ahora</a></p>
    </div>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach ($latestOffers as $offer): ?>
      <div class="offer-card fade-in">
        <?php if ($offer['image']): ?>
        <div class="offer-img">
          <img data-src="<?= Security::e($offer['image']) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="<?= Security::e($offer['title']) ?>">
        </div>
        <?php endif; ?>
        <div class="offer-body">
          <div class="offer-source"><i class="fas fa-external-link-alt"></i> <?= Security::e($offer['source']) ?></div>
          <h4><?= Security::e($offer['title']) ?></h4>
          <div class="offer-footer">
            <?php if ($offer['price']): ?>
            <span class="offer-badge"><?= Security::e($offer['price']) ?> €</span>
            <?php else: ?>
            <span></span>
            <?php endif; ?>
            <a href="<?= Security::e($offer['url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">
              Ver oferta <i class="fas fa-external-link-alt"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── CTA SELL ──────────────────────────────── -->
<section class="section">
  <div class="container">
    <div style="background:linear-gradient(135deg,var(--bg3),var(--bg2));border:1px solid var(--border-red);border-radius:20px;padding:60px;text-align:center;position:relative;overflow:hidden;">
      <div style="position:absolute;inset:0;background:radial-gradient(ellipse at center,rgba(232,25,44,.08),transparent);pointer-events:none;"></div>
      <div style="font-size:56px;margin-bottom:16px;">🏍️</div>
      <h2 style="font-family:var(--font-head);font-size:42px;font-weight:900;text-transform:uppercase;margin-bottom:12px;">¿Tienes algo que vender?</h2>
      <p style="color:var(--text-muted);max-width:500px;margin:0 auto 28px;font-size:16px;">Publica tu moto, equipo o repuestos gratis. Miles de moteros te están esperando.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="?page=sell" class="btn btn-red btn-lg">
          <i class="fas fa-plus-circle"></i> Publicar Gratis
        </a>
        <a href="?page=register" class="btn btn-outline btn-lg">
          <i class="fas fa-user-plus"></i> Crear Cuenta
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
