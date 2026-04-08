<?php
// ═══════════════════════════════════════════
//  REDLINECREW - User Profile Page
// ═══════════════════════════════════════════

Auth::requireLogin();
$db   = Database::getInstance();
$user = Auth::currentUser();
$pageTitle = 'Mi Perfil';

$myProducts  = $db->fetchAll("SELECT * FROM products WHERE user_id=? ORDER BY created_at DESC LIMIT 20", [$user['id']]);
$myFavorites = $db->fetchAll("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id=u.id JOIN favorites f ON f.product_id=p.id WHERE f.user_id=? ORDER BY f.created_at DESC LIMIT 12", [$user['id']]);
$stats = [
    'active'   => $db->fetch("SELECT COUNT(*) as c FROM products WHERE user_id=? AND status='active'",[$user['id']])['c'] ?? 0,
    'pending'  => $db->fetch("SELECT COUNT(*) as c FROM products WHERE user_id=? AND status='pending'",[$user['id']])['c'] ?? 0,
    'sold'     => $db->fetch("SELECT COUNT(*) as c FROM products WHERE user_id=? AND status='sold'",[$user['id']])['c'] ?? 0,
    'favorites'=> $db->fetch("SELECT COUNT(*) as c FROM favorites WHERE user_id=?",[$user['id']])['c'] ?? 0,
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <div style="display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start;">

    <!-- LEFT: Profile card -->
    <div class="sidebar">
      <div class="card" style="padding:28px;text-align:center;margin-bottom:16px;">
        <!-- Avatar -->
        <div style="width:80px;height:80px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-size:36px;font-weight:900;color:#fff;margin:0 auto 16px;">
          <?= strtoupper(substr($user['username'],0,1)) ?>
        </div>
        <h2 style="font-family:var(--font-head);font-size:24px;font-weight:900;"><?= Security::e($user['username']) ?></h2>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px;"><i class="fas fa-calendar-alt"></i> Desde <?= date('F Y', strtotime($user['created_at'])) ?></p>
        <?php if ($user['bio']): ?>
        <p style="font-size:14px;color:var(--text-dim);margin-top:12px;line-height:1.6;"><?= Security::e($user['bio']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Stats -->
      <div class="card" style="padding:20px;margin-bottom:16px;">
        <h4 style="font-family:var(--font-head);font-size:14px;text-transform:uppercase;color:var(--text-muted);margin-bottom:14px;">Estadísticas</h4>
        <?php foreach ([
          ['🏍️','Activos', $stats['active'], 'var(--green)'],
          ['⏳','Pendientes', $stats['pending'], 'var(--gold)'],
          ['✓','Vendidos', $stats['sold'], 'var(--blue)'],
          ['♥','Favoritos', $stats['favorites'], 'var(--red)'],
        ] as [$icon,$label,$val,$color]): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
          <span style="font-size:14px;"><?= $icon ?> <?= $label ?></span>
          <span style="font-family:var(--font-head);font-size:18px;font-weight:900;color:<?= $color ?>;"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Edit profile form -->
      <div class="card" style="padding:20px;">
        <h4 style="font-family:var(--font-head);font-size:14px;text-transform:uppercase;color:var(--text-muted);margin-bottom:16px;">
          <i class="fas fa-edit" style="color:var(--red);"></i> Editar Perfil
        </h4>
        <form method="POST" action="">
          <input type="hidden" name="form_action" value="update_profile">
          <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
          <div class="form-group">
            <label>Teléfono</label>
            <input type="tel" name="phone" class="form-control" value="<?= Security::e($user['phone'] ?? '') ?>" placeholder="+34 6XX XXX XXX">
          </div>
          <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Cuéntanos sobre ti y tus motos..." data-maxlength="300"><?= Security::e($user['bio'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-red btn-sm" style="width:100%;">Guardar cambios</button>
        </form>
      </div>
    </div>

    <!-- RIGHT: Tabs with products/favorites -->
    <div>
      <div class="tabs" data-group="profile">
        <button class="tab-btn active" data-tab="tab-products" data-tab-group="profile">Mis Anuncios <span style="background:var(--bg3);padding:2px 8px;border-radius:10px;font-size:12px;margin-left:4px;"><?= $stats['active']+$stats['pending'] ?></span></button>
        <button class="tab-btn" data-tab="tab-favorites" data-tab-group="profile">Favoritos <span style="background:var(--bg3);padding:2px 8px;border-radius:10px;font-size:12px;margin-left:4px;"><?= $stats['favorites'] ?></span></button>
      </div>

      <!-- My products tab -->
      <div id="tab-products" data-tab-group="profile">
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
          <a href="?page=sell" class="btn btn-red btn-sm"><i class="fas fa-plus"></i> Nuevo anuncio</a>
        </div>

        <?php if (empty($myProducts)): ?>
        <div style="text-align:center;padding:60px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
          <div style="font-size:48px;margin-bottom:12px;">📦</div>
          <h3 style="font-family:var(--font-head);font-size:22px;margin-bottom:8px;">Aún no tienes anuncios</h3>
          <a href="?page=sell" class="btn btn-red" style="margin-top:12px;">Publicar mi primer anuncio</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <?php foreach ($myProducts as $p):
            $images = json_decode($p['images'] ?? '[]', true);
            $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
            $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
            $statusColors = ['active'=>'var(--green)','pending'=>'var(--gold)','sold'=>'var(--blue)','rejected'=>'var(--red)'];
            $statusLabels = ['active'=>'Activo','pending'=>'Pendiente revisión','sold'=>'Vendido','rejected'=>'Rechazado'];
          ?>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;display:flex;gap:16px;align-items:center;">
            <!-- Thumb -->
            <div style="width:80px;height:80px;border-radius:var(--radius);overflow:hidden;flex-shrink:0;background:var(--bg3);display:flex;align-items:center;justify-content:center;">
              <?php if ($img): ?><img src="<?= Security::e($img) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?><span style="font-size:30px;"><?= $catInfo['icon'] ?></span><?php endif; ?>
            </div>
            <!-- Info -->
            <div style="flex:1;min-width:0;">
              <a href="?page=product&id=<?= $p['id'] ?>" style="font-weight:600;font-size:15px;color:var(--text);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= Security::e($p['title']) ?></a>
              <div style="display:flex;align-items:center;gap:12px;margin-top:6px;font-size:13px;color:var(--text-muted);">
                <span style="color:<?= $statusColors[$p['status']] ?? 'var(--text-muted)' ?>;font-weight:600;">● <?= $statusLabels[$p['status']] ?? $p['status'] ?></span>
                <span><i class="fas fa-eye"></i> <?= $p['views'] ?></span>
                <span><?= timeAgo($p['created_at']) ?></span>
              </div>
            </div>
            <!-- Price & actions -->
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-family:var(--font-head);font-size:20px;font-weight:900;color:var(--red);"><?= formatPrice($p['price']) ?></div>
              <div style="display:flex;gap:6px;margin-top:8px;">
                <a href="?page=edit-product&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i></a>
                <a href="api/products.php?action=delete&id=<?= $p['id'] ?>&csrf=<?= Security::csrfToken() ?>" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);" data-confirm="¿Eliminar este anuncio?"><i class="fas fa-trash"></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Favorites tab -->
      <div id="tab-favorites" data-tab-group="profile" class="hidden">
        <?php if (empty($myFavorites)): ?>
        <div style="text-align:center;padding:60px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
          <div style="font-size:48px;margin-bottom:12px;">♡</div>
          <h3 style="font-family:var(--font-head);font-size:22px;margin-bottom:8px;">Sin favoritos todavía</h3>
          <a href="?page=products" class="btn btn-red" style="margin-top:12px;">Explorar productos</a>
        </div>
        <?php else: ?>
        <div class="grid-4" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
          <?php foreach ($myFavorites as $p):
            $images = json_decode($p['images'] ?? '[]', true);
            $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
            $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
          ?>
          <div class="product-card">
            <a href="?page=product&id=<?= $p['id'] ?>"><div class="img-wrap"><?php if ($img): ?><img data-src="<?= Security::e($img) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt=""><?php else: ?><div class="img-placeholder"><?= $catInfo['icon'] ?></div><?php endif; ?></div></a>
            <div class="card-body">
              <h3><a href="?page=product&id=<?= $p['id'] ?>" style="color:inherit;"><?= Security::e($p['title']) ?></a></h3>
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
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
