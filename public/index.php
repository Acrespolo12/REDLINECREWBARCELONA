<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/REDLINECREWBARCELONA', '', $uri);
$uri = str_replace('/public', '', $uri);
$uri = trim($uri, '/');

if (empty($uri) || $uri === '') {
    header('Location: /REDLINECREWBARCELONA/public/pages/home.html');
    exit;
}

$routes = [
    'foro' => 'pages/foro.html',
    'rutas' => 'pages/rutas.html',
    'ofertas' => 'pages/ofertas.html',
    'perfil' => 'pages/perfil.html',
    'garage' => 'pages/garage.html',
    'admin' => '../admin/index.php',
    'api' => '../api/router.php',
];

foreach ($routes as $route => $file) {
    if (strpos($uri, $route) === 0) {
        if (file_exists($file)) {
            include $file;
        } else {
            http_response_code(404);
            echo "<h1>404 - Archivo no encontrado</h1>";
        }
        exit;
    }
}

http_response_code(404);
echo "<h1>404 - Página no encontrada</h1>";
?>