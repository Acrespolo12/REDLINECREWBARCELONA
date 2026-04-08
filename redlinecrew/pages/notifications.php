<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Notifications Page
// ═══════════════════════════════════════════
Auth::requireLogin();
$db = Database::getInstance();
$pageTitle = 'Notificaciones';
$uid = (int)$_SESSION['user_id'];

// Mark all as read
$db->query("UPDATE notifications SET is_read=1 WHERE user_id=?", [$uid]);

$notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50", [$uid]);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:32px;padding-bottom:60px;max-width:700px;">
  <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;margin-bottom:24px;">
    <i class="fas fa-bell" style="color:var(--red);"></i> Notificaciones
  </h1>
  <?php if (empty($notifications)): ?>
  <div style="text-align:center;padding:60px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
    <div style="font-size:48px;margin-bottom:12px;">🔔</div>
    <p style="color:var(--text-muted);">Sin notificaciones por ahora.</p>
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:8px;">
    <?php foreach ($notifications as $n):
      $icons = ['product_active'=>'✅','product_rejected'=>'❌','message'=>'💬','contact'=>'📧'];
      $icon = $icons[$n['type']] ?? '🔔';
    ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;display:flex;align-items:center;gap:14px;">
      <div style="font-size:24px;flex-shrink:0;"><?= $icon ?></div>
      <div style="flex:1;">
        <div style="font-size:14px;"><?= Security::e($n['message']) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;"><?= timeAgo($n['created_at']) ?></div>
      </div>
      <?php if ($n['link']): ?>
      <a href="<?= Security::e($n['link']) ?>" class="btn btn-ghost btn-sm">Ver</a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
