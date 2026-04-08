<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Configuration
//  Lee variables de entorno en Render,
//  usa valores por defecto en local
// ═══════════════════════════════════════════

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'redlinecrew');

define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'http://localhost/redlinecrew', '/'));
define('SITE_NAME', getenv('SITE_NAME') ?: 'REDLINECREW');
define('SITE_VERSION', '1.0.0');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

define('SESSION_LIFETIME', 3600 * 24 * 7);
define('BCRYPT_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

define('OFFERS_SOURCES', [
    ['name' => 'Motofichas',   'url' => 'https://www.motofichas.com/feed/',            'type' => 'rss'],
    ['name' => 'Solo Moto',    'url' => 'https://www.solomoto.es/rss.xml',             'type' => 'rss'],
    ['name' => 'MotorRaider',  'url' => 'https://www.motoraider.com/feed/',            'type' => 'rss'],
    ['name' => 'Moto1Pro',     'url' => 'https://moto1pro.com/feed/',                  'type' => 'rss'],
    ['name' => 'RevZilla',     'url' => 'https://www.revzilla.com/common-tread/feed/', 'type' => 'rss'],
    ['name' => 'Motociclismo', 'url' => 'https://www.motociclismo.es/rss.xml',         'type' => 'rss'],
]);

define('PRODUCT_CATEGORIES', [
    'cascos'      => ['name' => 'Cascos',               'icon' => '🪖'],
    'chaquetas'   => ['name' => 'Chaquetas & Ropa',     'icon' => '🧥'],
    'guantes'     => ['name' => 'Guantes',              'icon' => '🧤'],
    'botas'       => ['name' => 'Botas',                'icon' => '👢'],
    'motos'       => ['name' => 'Motos',                'icon' => '🏍️'],
    'accesorios'  => ['name' => 'Accesorios',           'icon' => '🔧'],
    'neumaticos'  => ['name' => 'Neumáticos',           'icon' => '⚫'],
    'escape'      => ['name' => 'Escapes',              'icon' => '💨'],
    'repuestos'   => ['name' => 'Repuestos',            'icon' => '⚙️'],
    'electronica' => ['name' => 'Electrónica',          'icon' => '📱'],
    'lubricantes' => ['name' => 'Lubricantes & Fluidos','icon' => '🛢️'],
    'maletas'     => ['name' => 'Maletas & Equipaje',   'icon' => '🎒'],
]);

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');

date_default_timezone_set('Europe/Madrid');
