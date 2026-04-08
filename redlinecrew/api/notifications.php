<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Notifications API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::isLoggedIn()) Security::jsonResponse(['ok' => false], 401);

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? '';
$csrf   = $input['csrf'] ?? $_GET['csrf'] ?? '';
$db     = Database::getInstance();
$uid    = (int)$_SESSION['user_id'];

switch ($action) {
    case 'count':
        $count = $db->fetch("SELECT COUNT(*) as c FROM notifications WHERE user_id=? AND is_read=0", [$uid])['c'] ?? 0;
        $msgs  = $db->fetch("SELECT COUNT(*) as c FROM messages WHERE receiver_id=? AND is_read=0", [$uid])['c'] ?? 0;
        Security::jsonResponse(['ok' => true, 'notifications' => (int)$count, 'messages' => (int)$msgs]);
        break;

    case 'mark_read':
        if (!Security::validateCsrf($csrf)) Security::jsonResponse(['ok' => false], 403);
        $db->query("UPDATE notifications SET is_read=1 WHERE user_id=?", [$uid]);
        Security::jsonResponse(['ok' => true]);
        break;

    default:
        $notifs = $db->fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20", [$uid]);
        Security::jsonResponse(['ok' => true, 'notifications' => $notifs]);
}
