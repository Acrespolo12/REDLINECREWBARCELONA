<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Offers API
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300'); // 5min cache

$db     = Database::getInstance();
$limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$cat    = preg_replace('/[^a-z_]/', '', strtolower($_GET['cat'] ?? ''));
$format = $_GET['format'] ?? 'full';

$where  = ['1=1'];
$params = [];
if ($cat) { $where[] = 'category = ?'; $params[] = $cat; }

$offers = $db->fetchAll(
    "SELECT id, title, url, source, price, category, image, pub_date FROM offers WHERE " . implode(' AND ', $where) . " ORDER BY pub_date DESC LIMIT ?",
    [...$params, $limit]
);

if ($format === 'ticker') {
    $offers = array_map(fn($o) => [
        'title'  => $o['title'],
        'url'    => $o['url'],
        'price'  => $o['price'],
        'source' => $o['source'],
    ], $offers);
}

Security::jsonResponse(['ok' => true, 'offers' => $offers, 'count' => count($offers)]);
