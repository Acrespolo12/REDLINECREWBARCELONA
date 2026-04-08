<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Search API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

$q     = trim($_GET['q'] ?? '');
$limit = min(20, max(1, (int)($_GET['limit'] ?? 8)));

if (strlen($q) < 2) {
    Security::jsonResponse(['results' => []]);
}

$db = Database::getInstance();

// Try FULLTEXT first, fallback to LIKE
try {
    $results = $db->fetchAll(
        "SELECT p.id, p.title, p.price, p.category, p.images
         FROM products p
         WHERE p.status='active' AND MATCH(p.title,p.description) AGAINST(? IN BOOLEAN MODE)
         ORDER BY MATCH(p.title,p.description) AGAINST(? IN BOOLEAN MODE) DESC
         LIMIT ?",
        [$q . '*', $q . '*', $limit]
    );
} catch (Exception $e) {
    $results = [];
}

if (empty($results)) {
    $results = $db->fetchAll(
        "SELECT id, title, price, category, images FROM products WHERE status='active' AND title LIKE ? LIMIT ?",
        ['%' . $q . '%', $limit]
    );
}

$formatted = array_map(function($p) {
    $images = json_decode($p['images'] ?? '[]', true);
    return [
        'id'       => $p['id'],
        'title'    => $p['title'],
        'price'    => $p['price'] ? number_format($p['price'], 0, ',', '.') : null,
        'category' => PRODUCT_CATEGORIES[$p['category']]['name'] ?? $p['category'],
        'image'    => !empty($images) ? UPLOAD_URL . $images[0] : null,
    ];
}, $results);

Security::jsonResponse(['results' => $formatted]);
