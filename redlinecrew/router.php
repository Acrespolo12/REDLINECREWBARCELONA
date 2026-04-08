<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Router para PHP built-in server
//  Usado por Render (reemplaza mod_rewrite de Apache)
// ═══════════════════════════════════════════

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Servir archivos estáticos directamente (CSS, JS, imágenes, uploads)
$staticExtensions = ['css','js','svg','png','jpg','jpeg','gif','webp','ico','woff','woff2','ttf','map'];
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

if (in_array($ext, $staticExtensions) && file_exists(__DIR__ . $uri)) {
    return false; // Servir el archivo tal cual
}

// Bloquear acceso directo a carpetas sensibles
$blocked = ['/includes/', '/logs/', '/.env', '/install.sql'];
foreach ($blocked as $b) {
    if (str_starts_with($uri, $b)) {
        http_response_code(403);
        echo '403 Forbidden';
        exit;
    }
}

// Rutas de la API
if (str_starts_with($uri, '/api/')) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && str_ends_with($file, '.php')) {
        require $file;
        exit;
    }
}

// Admin
if (str_starts_with($uri, '/admin/')) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && str_ends_with($file, '.php')) {
        require $file;
        exit;
    }
    // Si accede a /admin/ sin archivo, cargar index
    require __DIR__ . '/admin/index.php';
    exit;
}

// Uploads (imágenes subidas por usuarios)
if (str_starts_with($uri, '/uploads/')) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) return false;
    http_response_code(404);
    exit;
}

// Todo lo demás va al router principal
require __DIR__ . '/index.php';
