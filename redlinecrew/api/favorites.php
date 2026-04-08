<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Favorites API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    Security::jsonResponse(['ok' => false, 'login' => true]);
}

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$productId  = (int)($input['product_id'] ?? 0);
$csrf       = $input['csrf'] ?? '';

if (!Security::validateCsrf($csrf) || !$productId) {
    Security::jsonResponse(['ok' => false, 'msg' => 'Invalid request'], 400);
}

$db  = Database::getInstance();
$uid = (int)$_SESSION['user_id'];

$existing = $db->fetch("SELECT id FROM favorites WHERE user_id=? AND product_id=?", [$uid, $productId]);

if ($existing) {
    $db->delete('favorites', 'user_id=? AND product_id=?', [$uid, $productId]);
    Security::jsonResponse(['ok' => true, 'favorited' => false]);
} else {
    // Check product exists
    $product = $db->fetch("SELECT id FROM products WHERE id=? AND status='active'", [$productId]);
    if (!$product) Security::jsonResponse(['ok' => false, 'msg' => 'Product not found'], 404);

    $db->insert('favorites', [
        'user_id'    => $uid,
        'product_id' => $productId,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    Security::jsonResponse(['ok' => true, 'favorited' => true]);
}
