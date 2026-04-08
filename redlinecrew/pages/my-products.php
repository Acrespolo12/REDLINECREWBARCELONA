<?php
// ═══════════════════════════════════════════
//  REDLINECREW - My Products Page
// ═══════════════════════════════════════════
Auth::requireLogin();
$db = Database::getInstance();
$pageTitle = 'Mis Anuncios';
$uid = (int)$_SESSION['user_id'];

$filter = in_array($_GET['status']??'', ['active','pending','sold','rejected']) ? $_GET['status'] : '';
$where  = 'user_id = ?';
$params = [$uid];
if ($filter) { $where .= ' AND status = ?'; $params[] = $filter; }

$products = $db->fetchAll("SELECT * FROM products WHERE $where ORDER BY created_at DESC", $params);
$counts   = ['all'=>0,'active'=>0,'pending'=>0,'sold'=>0,'rejected'=>0];
foreach ($db->fetchAll("SELECT status, COUNT(*) as c FROM products WHERE user_id=? GROUP BY status", [$uid]) as $r) {
    $counts[$r['status']] = (int)$r['c'];
    $counts['all'] += (int)$r['c'];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;">Mis Anuncios</h1>
    <a href="?page=sell" class="btn btn-red"><i class="fas fa-plus"></i> Nuevo anuncio</a>
  </div>

  <!-- Status filter -->
  <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
    <?php foreach ([
      ['','Todos',$counts['all']],
      ['active','Activos',$counts['active'],'var(--green)'],
      ['pending','Pendientes',$counts['pending'],'var(--gold)'],
      ['sold','Vendidos',$counts['sold'],'var(--blue)'],
      ['rejected','Rechazados',$counts['rejected'],'var(--red)'],
    ] as [$val,$label,$cnt,$col??null]): ?>
    <a href="?page=my-products&status=<?= $val ?>"
       style="padding:8px 16px;background:<?= $filter===$val?'var(--red)':'var(--bg3)' ?>;color:<?= $filter===$val?'#fff':'var(--text-muted)' ?>;border:1px solid <?= $filter===$val?'var(--red)':'var(--border)' ?>;border-radius:20px;font-size:13px;display:flex;align-items:center;gap:6px;">
      <?= $label ?>
      <span style="background:rgba(255,255,255,.15);padding:1px 7px;border-radius:10px;"><?= $cnt ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($products)): ?>
  <div style="text-align:center;padding:80px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:16px;">📦</div>
    <h3 style="font-family:var(--font-head);font-size:24px;margin-bottom:8px;">Sin anuncios <?= $filter ? "\"$filter\"" : '' ?></h3>
    <a href="?page=sell" class="btn btn-red" style="margin-top:12px;">Publicar mi primer anuncio</a>
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($products as $p):
      $images = json_decode($p['images'] ?? '[]', true);
      $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
      $catInfo = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
      $statusColors = ['active'=>'var(--green)','pending'=>'var(--gold)','sold'=>'var(--blue)','rejected'=>'var(--red)'];
      $statusLabels = ['active'=>'✓ Activo','pending'=>'⏳ Pendiente revisión','sold'=>'🎉 Vendido','rejected'=>'✕ Rechazado'];
    ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px;display:flex;gap:16px;align-items:center;transition:.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="width:90px;height:90px;border-radius:var(--radius);overflow:hidden;flex-shrink:0;background:var(--bg3);display:flex;align-items:center;justify-content:center;">
        <?php if ($img): ?><img src="<?= Security::e($img) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?><span style="font-size:36px;"><?= $catInfo['icon'] ?></span><?php endif; ?>
      </div>
      <div style="flex:1;min-width:0;">
        <a href="?page=product&id=<?= $p['id'] ?>" style="font-weight:700;font-size:16px;color:var(--text);"><?= Security::e($p['title']) ?></a>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:6px;font-size:12px;color:var(--text-muted);">
          <span><?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?></span>
          <span>📍 <?= Security::e($p['location'] ?: 'España') ?></span>
          <span>👁 <?= $p['views'] ?> visitas</span>
          <span>🕒 <?= timeAgo($p['created_at']) ?></span>
        </div>
        <div style="margin-top:8px;">
          <span style="color:<?= $statusColors[$p['status']] ?? 'var(--text-muted)' ?>;font-size:13px;font-weight:600;"><?= $statusLabels[$p['status']] ?? $p['status'] ?></span>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0;">
        <div style="font-family:var(--font-head);font-size:24px;font-weight:900;color:var(--red);"><?= formatPrice($p['price']) ?></div>
        <div style="display:flex;gap:6px;justify-content:flex-end;margin-top:8px;">
          <a href="?page=product&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">👁</a>
          <a href="?page=edit-product&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i></a>
          <?php if ($p['status'] !== 'sold'): ?>
          <a href="api/products.php?action=mark_sold&id=<?= $p['id'] ?>&csrf=<?= Security::csrfToken() ?>" class="btn btn-sm" style="background:rgba(41,121,255,.15);color:var(--blue);" data-confirm="¿Marcar como vendido?">✓ Vendido</a>
          <?php endif; ?>
          <a href="api/products.php?action=delete&id=<?= $p['id'] ?>&csrf=<?= Security::csrfToken() ?>" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);" data-confirm="¿Eliminar este anuncio permanentemente?">🗑</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
