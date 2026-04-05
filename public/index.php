<?php

// Basic PHP Router

function route($uri) {
    switch ($uri) {
        case '/foro':
            include 'foro.php';
            break;
        case '/rutas':
            include 'rutas.php';
            break;
        case '/ofertas':
            include 'ofertas.php';
            break;
        case '/perfil':
            include 'perfil.php';
            break;
        case '/garage':
            include 'garage.php';
            break;
        case '/admin':
            include 'admin.php';
            break;
        case '/api':
            include 'api.php';
            break;
        default:
            http_response_code(404);
            include '404.php'; // 404 Not Found
            break;
    }
}

// Get the current URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Call the route function
route($requestUri);

?>