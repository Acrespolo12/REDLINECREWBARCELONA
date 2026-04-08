<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Admin API (AJAX)
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::isAdmin()) {
    Security::jsonResponse(['ok' => false, 'msg' => 'Unauthorized'], 403);
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';
$csrf   = $input['csrf'] ?? '';

if (!Security::validateCsrf($csrf)) {
    Security::jsonResponse(['ok' => false, 'msg' => 'CSRF token invalid'], 403);
}

$db = Database::getInstance();

switch ($action) {

    case 'update_product_status':
        $id     = (int)($input['id'] ?? 0);
        $status = in_array($input['status'] ?? '', ['active','pending','rejected','sold']) ? $input['status'] : null;
        if (!$id || !$status) Security::jsonResponse(['ok'=>false,'msg'=>'Invalid params'], 400);

        $db->update('products', ['status' => $status], 'id = ?', [$id]);

        // Notify seller if approved/rejected
        $product = $db->fetch("SELECT user_id, title FROM products WHERE id=?", [$id]);
        if ($product) {
            $msg = $status === 'active'
                ? "✅ Tu anuncio \"{$product['title']}\" ha sido aprobado y ya está visible."
                : "❌ Tu anuncio \"{$product['title']}\" ha sido rechazado. Contacta con nosotros si crees que es un error.";
            $db->insert('notifications', [
                'user_id'    => $product['user_id'],
                'type'       => 'product_' . $status,
                'message'    => $msg,
                'link'       => '?page=product&id=' . $id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Log
        $db->insert('activity_logs', [
            'user_id'    => $_SESSION['user_id'],
            'action'     => "product_status_$status",
            'details'    => "Product #$id → $status",
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Security::jsonResponse(['ok' => true]);
        break;

    case 'scrape_offers':
        require_once __DIR__ . '/../cron_offers.php';
        $scraper = new OffersScraper();
        $stats   = $scraper->run();
        $db->insert('activity_logs', [
            'user_id'    => $_SESSION['user_id'],
            'action'     => 'scrape_offers',
            'details'    => json_encode($stats),
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        Security::jsonResponse(['ok' => true, 'stats' => $stats]);
        break;

    case 'delete_user':
        $id = (int)($input['id'] ?? 0);
        if (!$id || $id == $_SESSION['user_id']) Security::jsonResponse(['ok'=>false,'msg'=>'Cannot delete'], 400);
        $db->delete('users', 'id = ?', [$id]);
        Security::jsonResponse(['ok' => true]);
        break;

    case 'toggle_featured':
        $id = (int)($input['id'] ?? 0);
        if (!$id) Security::jsonResponse(['ok'=>false,'msg'=>'No ID'], 400);
        $current = $db->fetch("SELECT featured FROM products WHERE id=?", [$id]);
        $new = $current ? ($current['featured'] ? 0 : 1) : 0;
        $db->update('products', ['featured' => $new], 'id = ?', [$id]);
        Security::jsonResponse(['ok' => true, 'featured' => (bool)$new]);
        break;

    default:
        Security::jsonResponse(['ok' => false, 'msg' => 'Unknown action'], 400);
}
