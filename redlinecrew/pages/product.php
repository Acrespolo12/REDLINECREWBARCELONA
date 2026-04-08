<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Single Product Page
// ═══════════════════════════════════════════

$db = Database::getInstance();
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('products');

$product = $db->fetch(
    "SELECT p.*, u.username, u.avatar, u.phone as user_phone, u.bio as user_bio, u.created_at as user_since
     FROM products p JOIN users u ON p.user_id = u.id
     WHERE p.id = ? AND (p.status = 'active' OR ? = p.user_id OR ? = 1)",
    [$id, $_SESSION['user_id'] ?? 0, $_SESSION['user_role'] === 'admin' ? 1 : 0]
);
if (!$product) { header('HTTP/1.0 404 Not Found'); require __DIR__.'/404.php'; exit; }

// Increment views (not for own products)
if (($_SESSION['user_id'] ?? 0) !== (int)$product['user_id']) {
    $db->query("UPDATE products SET views = views + 1 WHERE id = ?", [$id]);
}

$images  = json_decode($product['images'] ?? '[]', true);
$catInfo = PRODUCT_CATEGORIES[$product['category']] ?? ['name' => $product['category'], 'icon' => '🏍️'];
$isFav   = Auth::isLoggedIn() ? (bool)$db->fetch("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?", [$_SESSION['user_id'], $id]) : false;
$isOwner = Auth::isLoggedIn() && (int)$_SESSION['user_id'] === (int)$product['user_id'];

// Related products
$related = $db->fetchAll(
    "SELECT id, title, price, images, category FROM products WHERE status='active' AND category=? AND id != ? LIMIT 4",
    [$product['category'], $id]
);

// Reviews
$reviews = $db->fetchAll("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.product_id=? ORDER BY r.created_at DESC", [$id]);
$avgRating = $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;

$pageTitle = Security::e($product['title']);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <!-- Breadcrumb -->
  <div style="font-size:13px;color:var(--text-muted);margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <a href="?page=home">Inicio</a> /
    <a href="?page=products">Productos</a> /
    <a href="?page=products&cat=<?= $product['category'] ?>"><?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?></a> /
    <span style="color:var(--text);"><?= mb_substr(Security::e($product['title']), 0, 40) ?>...</span>
  </div>

  <div style="display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start;">
    <!-- LEFT: Images + Details -->
    <div>
      <!-- Image gallery -->
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:24px;">
        <!-- Main image -->
        <div id="main-img" style="aspect-ratio:4/3;background:var(--bg3);display:flex;align-items:center;justify-content:center;position:relative;">
          <?php if (!empty($images)): ?>
          <img src="<?= UPLOAD_URL . Security::e($images[0]) ?>" id="main-img-src" alt="<?= Security::e($product['title']) ?>" style="width:100%;height:100%;object-fit:contain;padding:8px;">
          <?php else: ?>
          <div style="font-size:80px;"><?= $catInfo['icon'] ?></div>
          <?php endif; ?>

          <!-- Status badge -->
          <?php if ($product['status'] === 'sold'): ?>
          <div style="position:absolute;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;">
            <span style="font-family:var(--font-head);font-size:40px;font-weight:900;color:#fff;transform:rotate(-20deg);border:4px solid #fff;padding:8px 20px;">VENDIDO</span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Thumbnails -->
        <?php if (count($images) > 1): ?>
        <div style="display:flex;gap:8px;padding:12px;background:var(--bg2);border-top:1px solid var(--border);overflow-x:auto;">
          <?php foreach ($images as $i => $img): ?>
          <div onclick="document.getElementById('main-img-src').src='<?= UPLOAD_URL . Security::e($img) ?>'"
               style="width:70px;height:70px;flex-shrink:0;border-radius:6px;overflow:hidden;cursor:pointer;border:2px solid <?= $i===0?'var(--red)':'var(--border)' ?>;transition:.2s;"
               onmouseover="this.style.borderColor='var(--red)'" onmouseout="this.style.borderColor='<?= $i===0?'var(--red)':'var(--border)' ?>'">
            <img src="<?= UPLOAD_URL . Security::e($img) ?>" style="width:100%;height:100%;object-fit:cover;">
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div class="card" style="padding:28px;margin-bottom:24px;">
        <h2 style="font-family:var(--font-head);font-size:20px;font-weight:700;margin-bottom:16px;text-transform:uppercase;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-align-left" style="color:var(--red);"></i> Descripción
        </h2>
        <p style="color:var(--text-dim);line-height:1.8;white-space:pre-wrap;"><?= nl2br(Security::e($product['description'])) ?></p>

        <!-- Details table -->
        <div style="margin-top:24px;border-top:1px solid var(--border);padding-top:20px;">
          <h3 style="font-family:var(--font-head);font-size:16px;font-weight:700;margin-bottom:16px;text-transform:uppercase;color:var(--text-dim);">Detalles</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <?php $details = [
              ['Categoría', $catInfo['icon'].' '.Security::e($catInfo['name'])],
              ['Estado', strip_tags(conditionLabel($product['condition_type']))],
              ['Ubicación', Security::e($product['location'] ?: 'España')],
              ['Publicado', timeAgo($product['created_at'])],
              ['Visitas', $product['views'].' veces'],
              ['Referencia', '#'.$product['id']],
            ]; foreach ($details as $d): ?>
            <div style="background:var(--bg3);padding:10px 14px;border-radius:var(--radius);">
              <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;"><?= $d[0] ?></div>
              <div style="font-size:14px;font-weight:600;"><?= $d[1] ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="card" style="padding:28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
          <h2 style="font-family:var(--font-head);font-size:20px;font-weight:700;text-transform:uppercase;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-star" style="color:var(--gold);"></i> Valoraciones
          </h2>
          <?php if ($avgRating): ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-family:var(--font-head);font-size:28px;font-weight:900;color:var(--gold);"><?= $avgRating ?></span>
            <div>
              <?php for ($s=1;$s<=5;$s++): ?>
              <span style="color:<?= $s<=$avgRating?'var(--gold)':'var(--text-muted)' ?>;">★</span>
              <?php endfor; ?>
              <div style="font-size:12px;color:var(--text-muted);"><?= count($reviews) ?> valoraciones</div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <?php if (Auth::isLoggedIn() && !$isOwner): ?>
        <form method="POST" action="api/reviews.php" style="background:var(--bg3);padding:16px;border-radius:var(--radius-lg);margin-bottom:20px;">
          <input type="hidden" name="product_id" value="<?= $id ?>">
          <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
          <div style="display:flex;gap:8px;margin-bottom:12px;font-size:24px;" id="star-select">
            <?php for($s=1;$s<=5;$s++): ?>
            <label style="cursor:pointer;"><input type="radio" name="rating" value="<?= $s ?>" required style="display:none;">
              <span class="star-btn" data-val="<?= $s ?>" style="color:var(--text-muted);transition:.15s;">★</span>
            </label>
            <?php endfor; ?>
          </div>
          <textarea name="comment" class="form-control" placeholder="Escribe tu valoración (opcional)" rows="2"></textarea>
          <button type="submit" class="btn btn-red btn-sm" style="margin-top:10px;">Enviar valoración</button>
        </form>
        <script>
          document.querySelectorAll('.star-btn').forEach(btn => {
            btn.addEventListener('mouseover', () => { let v=btn.dataset.val; document.querySelectorAll('.star-btn').forEach(b=>{b.style.color=b.dataset.val<=v?'var(--gold)':'var(--text-muted)'}); });
            btn.addEventListener('click', () => { let v=btn.dataset.val; document.querySelectorAll('.star-btn').forEach(b=>{b.style.color=b.dataset.val<=v?'var(--gold)':'var(--text-muted)'}); });
          });
        </script>
        <?php endif; ?>

        <?php foreach ($reviews as $r): ?>
        <div style="padding:14px 0;border-bottom:1px solid var(--border);">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
            <div style="width:30px;height:30px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;font-size:13px;color:#fff;"><?= strtoupper(substr($r['username'],0,1)) ?></div>
            <strong style="font-size:14px;"><?= Security::e($r['username']) ?></strong>
            <span style="color:var(--gold);"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5-$r['rating']) ?></span>
            <span style="margin-left:auto;font-size:12px;color:var(--text-muted);"><?= timeAgo($r['created_at']) ?></span>
          </div>
          <?php if ($r['comment']): ?><p style="font-size:14px;color:var(--text-dim);"><?= Security::e($r['comment']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><p style="color:var(--text-muted);text-align:center;padding:20px;">Sin valoraciones todavía.</p><?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Price + Contact -->
    <div class="sidebar">
      <!-- Price card -->
      <div class="card" style="padding:24px;margin-bottom:16px;">
        <div style="font-family:var(--font-head);font-size:42px;font-weight:900;color:var(--red);margin-bottom:4px;">
          <?= formatPrice($product['price']) ?>
        </div>
        <?= conditionLabel($product['condition_type']) ?>

        <h1 style="font-size:20px;font-weight:600;margin-top:14px;line-height:1.3;"><?= Security::e($product['title']) ?></h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:6px;">
          <i class="fas fa-map-marker-alt"></i> <?= Security::e($product['location'] ?: 'España') ?>
          &nbsp;•&nbsp;
          <i class="fas fa-clock"></i> <?= timeAgo($product['created_at']) ?>
        </p>

        <?php if ($product['status'] !== 'sold'): ?>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
          <?php if ($product['contact_whatsapp']): ?>
          <a href="https://wa.me/<?= preg_replace('/\D/','',$product['contact_whatsapp']) ?>?text=Hola, me interesa tu anuncio: <?= urlencode($product['title']) ?>"
             target="_blank" class="btn" style="background:#25d366;color:#fff;width:100%;justify-content:center;">
            <i class="fab fa-whatsapp"></i> WhatsApp
          </a>
          <?php endif; ?>
          <?php if ($product['contact_phone']): ?>
          <a href="tel:<?= preg_replace('/\s/','',$product['contact_phone']) ?>" class="btn btn-ghost" style="width:100%;justify-content:center;">
            <i class="fas fa-phone"></i> <?= Security::e($product['contact_phone']) ?>
          </a>
          <?php endif; ?>
          <?php if ($product['contact_email']): ?>
          <a href="mailto:<?= Security::e($product['contact_email']) ?>?subject=Interesado en: <?= urlencode($product['title']) ?>" class="btn btn-ghost" style="width:100%;justify-content:center;">
            <i class="fas fa-envelope"></i> Enviar email
          </a>
          <?php endif; ?>
          <?php if (Auth::isLoggedIn() && !$isOwner): ?>
          <a href="?page=messages&user=<?= $product['user_id'] ?>&product=<?= $id ?>" class="btn btn-outline" style="width:100%;justify-content:center;">
            <i class="fas fa-comments"></i> Mensaje interno
          </a>
          <?php endif; ?>
          <button class="fav-btn <?= $isFav?'active':'' ?>" data-id="<?= $id ?>" style="width:100%;padding:10px;border-radius:var(--radius);background:var(--bg3);border:1px solid var(--border);color:var(--text-muted);font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
            <?= $isFav ? '♥ En favoritos' : '♡ Añadir a favoritos' ?>
          </button>
        </div>
        <?php else: ?>
        <div style="background:rgba(232,25,44,.1);border:1px solid var(--border-red);border-radius:var(--radius);padding:14px;text-align:center;margin-top:16px;color:var(--red);font-family:var(--font-head);font-weight:700;font-size:18px;">
          VENDIDO
        </div>
        <?php endif; ?>

        <?php if ($isOwner): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:8px;">
          <a href="?page=edit-product&id=<?= $id ?>" class="btn btn-ghost btn-sm" style="flex:1;justify-content:center;"><i class="fas fa-edit"></i> Editar</a>
          <a href="api/products.php?action=mark_sold&id=<?= $id ?>&csrf=<?= Security::csrfToken() ?>" class="btn btn-red btn-sm" style="flex:1;justify-content:center;" onclick="return confirm('¿Marcar como vendido?')">✓ Vendido</a>
        </div>
        <?php endif; ?>

        <?php if (Auth::isAdmin()): ?>
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
          <button onclick="RLC.updateProductStatus(<?= $id ?>,'active')" class="btn btn-sm btn-ghost" style="flex:1;">✓ Aprobar</button>
          <button onclick="RLC.updateProductStatus(<?= $id ?>,'rejected')" class="btn btn-sm" style="flex:1;background:rgba(232,25,44,.15);color:var(--red);">✕ Rechazar</button>
        </div>
        <?php endif; ?>
      </div>

      <!-- Seller card -->
      <div class="card" style="padding:20px;">
        <h3 style="font-family:var(--font-head);font-size:15px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:14px;">Vendedor</h3>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
          <div style="width:44px;height:44px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:900;font-size:20px;color:#fff;flex-shrink:0;">
            <?= strtoupper(substr($product['username'],0,1)) ?>
          </div>
          <div>
            <div style="font-weight:600;font-size:16px;"><?= Security::e($product['username']) ?></div>
            <div style="font-size:12px;color:var(--text-muted);">Miembro desde <?= date('M Y', strtotime($product['user_since'])) ?></div>
          </div>
        </div>
        <?php
        $sellerProducts = (int)$db->fetch("SELECT COUNT(*) as c FROM products WHERE user_id=? AND status='active'", [$product['user_id']])['c'];
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
          <div style="text-align:center;background:var(--bg3);padding:10px;border-radius:var(--radius);">
            <div style="font-family:var(--font-head);font-size:20px;font-weight:900;color:var(--red);"><?= $sellerProducts ?></div>
            <div style="font-size:11px;color:var(--text-muted);">Anuncios activos</div>
          </div>
          <div style="text-align:center;background:var(--bg3);padding:10px;border-radius:var(--radius);">
            <div style="font-family:var(--font-head);font-size:20px;font-weight:900;color:var(--gold);"><?= $avgRating ?: '–' ?></div>
            <div style="font-size:11px;color:var(--text-muted);">Valoración</div>
          </div>
        </div>
        <a href="?page=products&seller=<?= $product['user_id'] ?>" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:12px;">Ver otros anuncios</a>
      </div>

      <!-- Share -->
      <div class="card" style="padding:16px;margin-top:16px;">
        <h4 style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Compartir</h4>
        <div style="display:flex;gap:8px;">
          <a href="https://wa.me/?text=<?= urlencode(SITE_URL.'/?page=product&id='.$id) ?>" target="_blank" style="flex:1;background:#25d366;color:#fff;padding:8px;border-radius:var(--radius);text-align:center;font-size:13px;"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL.'/?page=product&id='.$id) ?>" target="_blank" style="flex:1;background:#1877f2;color:#fff;padding:8px;border-radius:var(--radius);text-align:center;font-size:13px;"><i class="fab fa-facebook"></i></a>
          <button onclick="RLC.copyToClipboard('<?= SITE_URL.'/?page=product&id='.$id ?>')" style="flex:1;background:var(--bg3);color:var(--text);padding:8px;border-radius:var(--radius);font-size:13px;border:1px solid var(--border);">🔗 Copiar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Related products -->
  <?php if (!empty($related)): ?>
  <div style="margin-top:48px;">
    <div class="section-header">
      <h2 class="section-title">También te puede interesar</h2>
    </div>
    <div class="grid-4">
      <?php foreach ($related as $r):
        $rImages = json_decode($r['images'] ?? '[]', true);
        $rImg = !empty($rImages) ? UPLOAD_URL . $rImages[0] : null;
        $rCat = PRODUCT_CATEGORIES[$r['category']] ?? ['name' => $r['category'], 'icon' => '🏍️'];
      ?>
      <div class="product-card">
        <a href="?page=product&id=<?= $r['id'] ?>">
          <div class="img-wrap">
            <?php if ($rImg): ?>
            <img data-src="<?= Security::e($rImg) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="<?= Security::e($r['title']) ?>">
            <?php else: ?>
            <div class="img-placeholder"><?= $rCat['icon'] ?></div>
            <?php endif; ?>
          </div>
        </a>
        <div class="card-body">
          <div class="card-cat"><?= $rCat['icon'] ?> <?= Security::e($rCat['name']) ?></div>
          <h3><a href="?page=product&id=<?= $r['id'] ?>" style="color:inherit;"><?= Security::e($r['title']) ?></a></h3>
          <div class="card-price"><?= formatPrice($r['price']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
