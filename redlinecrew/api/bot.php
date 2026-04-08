<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Bot API Endpoint
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Security::jsonResponse(['ok' => false], 405);
}

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim($input['message'] ?? '');
$csrf    = $input['csrf'] ?? '';

if (!Security::validateCsrf($csrf) || empty($message)) {
    Security::jsonResponse(['ok' => false, 'response' => 'Error de validación.'], 400);
}

// Rate limit bot (5 messages per 30 seconds per IP)
$key = 'bot_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
// Simple check
$db = Database::getInstance();
$recent = $db->fetch(
    "SELECT COUNT(*) as c FROM bot_conversations WHERE session_id LIKE ? AND role='user' AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)",
    [$_SERVER['REMOTE_ADDR'] . '%']
);
if (($recent['c'] ?? 0) > 10) {
    Security::jsonResponse(['ok' => true, 'response' => '⏳ Vas muy rápido. Espera un momento antes de enviar más mensajes.']);
}

// Session ID for bot
$sessionId = ($_SERVER['REMOTE_ADDR'] ?? 'anon') . '_' . (session_id() ?: 'nosession');

$bot      = new RedBot($sessionId, Auth::isLoggedIn() ? $_SESSION['user_id'] : null);
$response = $bot->respond($message);

Security::jsonResponse(['ok' => true, 'response' => $response]);
