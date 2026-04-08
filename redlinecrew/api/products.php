<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Products API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
Auth::requireLogin();

$db     = Database::getInstance();
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);
$csrf   = $_GET['csrf'] ?? '';

if (!Security::validateCsrf($csrf)) {
    flash('error', 'Token inválido');
    redirect('home');
}

if (!$id) redirect('my-products');

$product = $db->fetch("SELECT * FROM products WHERE id=?", [$id]);
if (!$product) redirect('my-products');

$isOwner = (int)$product['user_id'] === (int)$_SESSION['user_id'];
$isAdmin = Auth::isAdmin();

if (!$isOwner && !$isAdmin) {
    flash('error', 'No tienes permisos para esta acción');
    redirect('home');
}

switch ($action) {
    case 'mark_sold':
        $db->update('products', ['status' => 'sold'], 'id = ?', [$id]);
        flash('success', '¡Enhorabuena! Anuncio marcado como vendido 🎉');
        redirect('product', ['id' => $id]);
        break;

    case 'delete':
        // Delete images
        $images = json_decode($product['images'] ?? '[]', true);
        foreach ($images as $img) {
            $path = UPLOAD_DIR . $img;
            if (file_exists($path)) unlink($path);
        }
        $db->delete('products', 'id = ?', [$id]);
        flash('success', 'Anuncio eliminado');
        redirect('my-products');
        break;

    case 'reactivate':
        if ($isAdmin || $isOwner) {
            $db->update('products', ['status' => 'pending'], 'id = ?', [$id]);
            flash('success', 'Anuncio enviado de nuevo a revisión');
        }
        redirect('my-products');
        break;

    default:
        redirect('my-products');
}
