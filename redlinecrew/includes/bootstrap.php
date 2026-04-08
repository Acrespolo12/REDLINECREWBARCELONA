<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Bootstrap
// ═══════════════════════════════════════════

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/bot.php';

// Session secure
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

Security::setSecurityHeaders();

// Helper functions
function asset(string $path): string {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $page = '', array $params = []): string {
    $q = $page ? ['page' => $page] : [];
    $q = array_merge($q, $params);
    return SITE_URL . '/?' . http_build_query($q);
}

function redirect(string $page, array $params = []): never {
    header('Location: ' . url($page, $params));
    exit;
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60     => 'hace un momento',
        $diff < 3600   => 'hace ' . floor($diff / 60) . 'm',
        $diff < 86400  => 'hace ' . floor($diff / 3600) . 'h',
        $diff < 604800 => 'hace ' . floor($diff / 86400) . ' días',
        default        => date('d/m/Y', strtotime($datetime)),
    };
}

function formatPrice(float $price): string {
    return number_format($price, 2, ',', '.') . ' €';
}

function conditionLabel(string $c): string {
    return match($c) {
        'nuevo'      => '<span class="badge badge-new">Nuevo</span>',
        'como_nuevo' => '<span class="badge badge-like-new">Como nuevo</span>',
        'bueno'      => '<span class="badge badge-good">Buen estado</span>',
        'aceptable'  => '<span class="badge badge-ok">Aceptable</span>',
        default      => '<span class="badge">Usado</span>',
    };
}

// Notifications count for logged users
function unreadNotifications(): int {
    if (!Auth::isLoggedIn()) return 0;
    $db = Database::getInstance();
    return (int)($db->fetch(
        "SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0",
        [$_SESSION['user_id']]
    )['c'] ?? 0);
}

function unreadMessages(): int {
    if (!Auth::isLoggedIn()) return 0;
    $db = Database::getInstance();
    return (int)($db->fetch(
        "SELECT COUNT(*) as c FROM messages WHERE receiver_id = ? AND is_read = 0",
        [$_SESSION['user_id']]
    )['c'] ?? 0);
}
