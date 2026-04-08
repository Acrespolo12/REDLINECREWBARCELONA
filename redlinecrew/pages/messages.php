<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Messages Page
// ═══════════════════════════════════════════

Auth::requireLogin();
$db = Database::getInstance();
$pageTitle = 'Mensajes';
$uid = (int)$_SESSION['user_id'];

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }
    $toId   = (int)($_POST['to_id'] ?? 0);
    $prodId = (int)($_POST['product_id'] ?? 0) ?: null;
    $msg    = trim($_POST['message'] ?? '');
    if ($toId && $msg && $toId !== $uid) {
        $db->insert('messages', [
            'sender_id'   => $uid,
            'receiver_id' => $toId,
            'product_id'  => $prodId,
            'content'     => mb_substr($msg, 0, 2000),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        // Notification
        $senderName = $_SESSION['user_name'];
        $db->insert('notifications', [
            'user_id'    => $toId,
            'type'       => 'message',
            'message'    => "Nuevo mensaje de $senderName",
            'link'       => '?page=messages&with='.$uid,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if (isset($_POST['ajax'])) { echo json_encode(['ok' => true]); exit; }
    }
    redirect('messages', ['with' => $toId]);
}

// Current conversation partner
$withId = (int)($_GET['with'] ?? 0);
$prodId = (int)($_GET['product'] ?? 0);

// Get all conversations
$conversations = $db->fetchAll(
    "SELECT DISTINCT
       CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as partner_id,
       u.username as partner_name,
       (SELECT content FROM messages WHERE (sender_id=? AND receiver_id=u.id) OR (sender_id=u.id AND receiver_id=?) ORDER BY created_at DESC LIMIT 1) as last_msg,
       (SELECT created_at FROM messages WHERE (sender_id=? AND receiver_id=u.id) OR (sender_id=u.id AND receiver_id=?) ORDER BY created_at DESC LIMIT 1) as last_time,
       (SELECT COUNT(*) FROM messages WHERE receiver_id=? AND sender_id=u.id AND is_read=0) as unread
     FROM messages m
     JOIN users u ON u.id = CASE WHEN m.sender_id=? THEN m.receiver_id ELSE m.sender_id END
     WHERE m.sender_id=? OR m.receiver_id=?
     ORDER BY last_time DESC",
    [$uid,$uid,$uid,$uid,$uid,$uid,$uid,$uid,$uid]
);

// Active conversation
$conversation = [];
$partner = null;
if ($withId) {
    $partner = $db->fetch("SELECT id, username FROM users WHERE id=?", [$withId]);
    // Mark as read
    $db->query("UPDATE messages SET is_read=1 WHERE receiver_id=? AND sender_id=?", [$uid, $withId]);
    $conversation = $db->fetchAll(
        "SELECT m.*, u.username as sender_name FROM messages m JOIN users u ON u.id=m.sender_id
         WHERE (m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?)
         ORDER BY m.created_at ASC LIMIT 100",
        [$uid,$withId,$withId,$uid]
    );
    // Product reference
    $productRef = $prodId ? $db->fetch("SELECT id, title, price FROM products WHERE id=?", [$prodId]) : null;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;margin-bottom:24px;">
    <i class="fas fa-comments" style="color:var(--red);"></i> Mensajes
  </h1>

  <div style="display:grid;grid-template-columns:300px 1fr;gap:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;height:600px;">

    <!-- Conversations list -->
    <div style="border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;">
      <div style="padding:16px;border-bottom:1px solid var(--border);font-family:var(--font-head);font-size:14px;font-weight:700;text-transform:uppercase;color:var(--text-muted);">
        Conversaciones
      </div>
      <div style="overflow-y:auto;flex:1;">
        <?php if (empty($conversations)): ?>
        <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:14px;">
          Sin conversaciones todavía
        </div>
        <?php else: ?>
        <?php foreach ($conversations as $conv): ?>
        <a href="?page=messages&with=<?= $conv['partner_id'] ?>"
           style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.04);transition:.2s;background:<?= $conv['partner_id']==$withId?'rgba(232,25,44,.08)':'' ?>;color:var(--text);"
           onmouseover="this.style.background='var(--bg3)'" onmouseout="this.style.background='<?= $conv['partner_id']==$withId?'rgba(232,25,44,.08)':'' ?>'">
          <div style="width:40px;height:40px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;color:#fff;flex-shrink:0;">
            <?= strtoupper(substr($conv['partner_name'],0,1)) ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <strong style="font-size:14px;"><?= Security::e($conv['partner_name']) ?></strong>
              <span style="font-size:11px;color:var(--text-muted);"><?= timeAgo($conv['last_time']) ?></span>
            </div>
            <div style="font-size:12px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
              <?= Security::e(mb_substr($conv['last_msg'] ?? '', 0, 40)) ?>
            </div>
          </div>
          <?php if ($conv['unread'] > 0): ?>
          <span style="background:var(--red);color:#fff;font-size:10px;font-weight:700;border-radius:10px;padding:2px 6px;min-width:18px;text-align:center;"><?= $conv['unread'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Conversation area -->
    <?php if ($partner): ?>
    <div style="display:flex;flex-direction:column;overflow:hidden;">
      <!-- Header -->
      <div style="padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
        <div style="width:36px;height:36px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;color:#fff;"><?= strtoupper(substr($partner['username'],0,1)) ?></div>
        <strong><?= Security::e($partner['username']) ?></strong>
        <?php if (!empty($productRef)): ?>
        <span style="margin-left:auto;font-size:12px;background:var(--bg3);padding:4px 12px;border-radius:20px;border:1px solid var(--border);">
          📦 <a href="?page=product&id=<?= $productRef['id'] ?>" style="color:var(--text-dim);"><?= Security::e(mb_substr($productRef['title'],0,30)) ?></a>
          — <strong style="color:var(--red);"><?= formatPrice($productRef['price']) ?></strong>
        </span>
        <?php endif; ?>
      </div>

      <!-- Messages -->
      <div id="msg-area" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($conversation as $m):
          $isMe = $m['sender_id'] == $uid;
        ?>
        <div style="display:flex;justify-content:<?= $isMe?'flex-end':'flex-start' ?>;">
          <div style="max-width:70%;">
            <div style="background:<?= $isMe?'var(--red)':'var(--bg3)' ?>;color:<?= $isMe?'#fff':'var(--text)' ?>;padding:10px 14px;border-radius:<?= $isMe?'14px 14px 4px 14px':'14px 14px 14px 4px' ?>;font-size:14px;line-height:1.5;">
              <?= nl2br(Security::e($m['content'])) ?>
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px;text-align:<?= $isMe?'right':'left' ?>;">
              <?= timeAgo($m['created_at']) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($conversation)): ?>
        <div style="text-align:center;color:var(--text-muted);font-size:14px;margin-top:32px;">
          Inicia la conversación 💬
        </div>
        <?php endif; ?>
      </div>

      <!-- Input -->
      <form method="POST" style="padding:14px;border-top:1px solid var(--border);display:flex;gap:10px;">
        <input type="hidden" name="send_message" value="1">
        <input type="hidden" name="to_id" value="<?= $withId ?>">
        <input type="hidden" name="product_id" value="<?= $prodId ?>">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <input type="text" name="message" id="msg-input" class="form-control" placeholder="Escribe un mensaje..." autocomplete="off" maxlength="2000" required style="flex:1;">
        <button type="submit" class="btn btn-red"><i class="fas fa-paper-plane"></i></button>
      </form>
    </div>
    <?php else: ?>
    <div style="display:flex;align-items:center;justify-content:center;color:var(--text-muted);">
      <div style="text-align:center;">
        <div style="font-size:56px;margin-bottom:12px;">💬</div>
        <p>Selecciona una conversación para empezar</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Auto-scroll to bottom
const msgArea = document.getElementById('msg-area');
if (msgArea) msgArea.scrollTop = msgArea.scrollHeight;
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
