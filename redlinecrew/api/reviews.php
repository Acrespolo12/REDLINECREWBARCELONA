<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Reviews API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('home'); }

$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = trim($_POST['comment'] ?? '');
$csrf      = $_POST['csrf_token'] ?? '';

if (!Security::validateCsrf($csrf)) {
    flash('error', 'Token de seguridad inválido'); redirect('product', ['id' => $productId]);
}
if (!$productId || $rating < 1 || $rating > 5) {
    flash('error', 'Valoración inválida'); redirect('product', ['id' => $productId]);
}

$db  = Database::getInstance();
$uid = (int)$_SESSION['user_id'];

// Can't review own product
$product = $db->fetch("SELECT user_id FROM products WHERE id=? AND status='active'", [$productId]);
if (!$product) { flash('error', 'Producto no encontrado'); redirect('products'); }
if ($product['user_id'] == $uid) { flash('error', 'No puedes valorar tu propio anuncio'); redirect('product', ['id' => $productId]); }

// Check already reviewed
$existing = $db->fetch("SELECT id FROM reviews WHERE product_id=? AND user_id=?", [$productId, $uid]);
if ($existing) {
    // Update
    $db->update('reviews', ['rating' => $rating, 'comment' => mb_substr($comment, 0, 1000)], 'product_id=? AND user_id=?', [$productId, $uid]);
    flash('success', 'Valoración actualizada');
} else {
    $db->insert('reviews', [
        'product_id' => $productId,
        'user_id'    => $uid,
        'rating'     => $rating,
        'comment'    => mb_substr($comment, 0, 1000),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    flash('success', 'Valoración enviada ⭐');
}

redirect('product', ['id' => $productId]);
