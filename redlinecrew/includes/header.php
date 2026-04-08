<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Header Template
// ═══════════════════════════════════════════
if (!defined('SITE_NAME')) die('Access denied');
$csrf    = Security::csrfToken();
$isAdmin = Auth::isAdmin();
$loggedIn = Auth::isLoggedIn();
$notifs  = unreadNotifications();
$msgs    = unreadMessages();
$currentPage = $_GET['page'] ?? 'home';
$searchQ = Security::e($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= $csrf ?>">
  <title><?= isset($pageTitle) ? Security::e($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? Security::e($pageDesc) : 'La comunidad motera definitiva. Compra, vende, descubre las mejores ofertas en motos y accesorios.' ?>">
  <meta name="theme-color" content="#e8192c">
  <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,900;1,700&family=Barlow:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>

<!-- ── OFFERS TICKER ─────────────────────── -->
<div style="background:rgba(232,25,44,0.08);border-bottom:1px solid rgba(232,25,44,0.2);padding:8px 0;overflow:hidden;white-space:nowrap;">
  <div style="display:flex;align-items:center;gap:16px;padding:0 20px;">
    <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#e8192c;letter-spacing:2px;text-transform:uppercase;flex-shrink:0;">
      <span class="live-dot"></span> LIVE OFFERS
    </span>
    <div id="offers-ticker" style="flex:1;overflow:hidden;font-size:13px;color:var(--text-dim);">
      <span style="color:var(--text-muted)">Cargando ofertas...</span>
    </div>
    <style>
      .ticker-item { margin-right: 24px; }
      .ticker-item a { color: var(--text-dim); }
      .ticker-item a:hover { color: var(--red); }
      .ticker-sep { margin: 0 8px; color: var(--border); }
    </style>
  </div>
</div>

<!-- ── NAVBAR ──────────────────────────────── -->
<nav class="navbar">
  <div class="container navbar-inner">

    <!-- Logo -->
    <a href="<?= SITE_URL ?>" class="logo">
      <div class="logo-line"></div>
      RED<span>LINE</span>CREW
    </a>

    <!-- Nav Links -->
    <ul class="nav-links">
      <li><a href="?page=home" class="<?= $currentPage==='home'?'active':'' ?>">
        <i class="fas fa-home"></i> Inicio
      </a></li>
      <li><a href="?page=categories" class="<?= $currentPage==='categories'?'active':'' ?>">
        <i class="fas fa-th"></i> Categorías
      </a></li>
      <li><a href="?page=products" class="<?= $currentPage==='products'?'active':'' ?>">
        <i class="fas fa-store"></i> Productos
      </a></li>
      <li><a href="?page=offers" class="<?= $currentPage==='offers'?'active':'' ?>" style="color:var(--gold)">
        <i class="fas fa-bolt"></i> Ofertas
      </a></li>
      <li><a href="?page=sell" class="<?= $currentPage==='sell'?'active':'' ?>" style="color:var(--green)">
        <i class="fas fa-plus-circle"></i> Vender
      </a></li>
      <li><a href="?page=contact" class="<?= $currentPage==='contact'?'active':'' ?>">
        <i class="fas fa-envelope"></i> Contacto
      </a></li>
    </ul>

    <!-- Right side -->
    <div class="nav-right">
      <!-- Search -->
      <div class="nav-search" style="position:relative;">
        <i class="fas fa-search" style="color:var(--text-muted);font-size:14px;"></i>
        <input type="text" id="main-search" placeholder="Buscar..." value="<?= $searchQ ?>" autocomplete="off">
        <div id="search-dropdown" style="position:absolute;top:calc(100% + 8px);left:0;right:0;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow);z-index:500;display:none;overflow:hidden;">
          <style>
            .search-result-item { display:flex;align-items:center;gap:10px;padding:10px 14px;color:var(--text);border-bottom:1px solid var(--border);font-size:14px;transition:.2s; }
            .search-result-item:last-child { border-bottom:none; }
            .search-result-item:hover { background:var(--bg3); }
            .search-cat { font-size:10px;background:rgba(232,25,44,.15);color:var(--red);padding:2px 8px;border-radius:10px;white-space:nowrap; }
            .search-price { margin-left:auto;color:var(--red);font-weight:700;white-space:nowrap; }
          </style>
        </div>
      </div>

      <?php if ($loggedIn): ?>
        <!-- Notifications -->
        <a href="?page=notifications" title="Notificaciones" style="color:var(--text-muted);font-size:18px;position:relative;">
          <i class="fas fa-bell"></i>
          <?php if ($notifs > 0): ?><span class="badge-count"><?= $notifs ?></span><?php endif; ?>
        </a>
        <!-- Messages -->
        <a href="?page=messages" title="Mensajes" style="color:var(--text-muted);font-size:18px;position:relative;">
          <i class="fas fa-comments"></i>
          <?php if ($msgs > 0): ?><span class="badge-count"><?= $msgs ?></span><?php endif; ?>
        </a>
        <!-- User dropdown -->
        <div style="position:relative;">
          <button data-dropdown="user-menu" style="background:var(--bg3);border:1px solid var(--border);border-radius:20px;padding:6px 12px 6px 8px;color:var(--text);display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
            <div style="width:28px;height:28px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;font-family:var(--font-head);">
              <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <?= Security::e($_SESSION['user_name'] ?? '') ?>
            <i class="fas fa-chevron-down" style="font-size:10px;color:var(--text-muted);"></i>
          </button>
          <div id="user-menu" class="dropdown-menu" style="position:absolute;right:0;top:calc(100%+8px);background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);min-width:180px;box-shadow:var(--shadow);z-index:500;overflow:hidden;display:none;">
            <style>.dropdown-menu.open{display:block!important;}.dropdown-menu a{display:flex;align-items:center;gap:10px;padding:10px 16px;color:var(--text-dim);font-size:14px;}.dropdown-menu a:hover{background:var(--bg3);color:var(--text);}</style>
            <a href="?page=profile"><i class="fas fa-user" style="width:16px;text-align:center;"></i> Mi Perfil</a>
            <a href="?page=my-products"><i class="fas fa-box" style="width:16px;text-align:center;"></i> Mis Anuncios</a>
            <a href="?page=favorites"><i class="fas fa-heart" style="width:16px;text-align:center;color:var(--red);"></i> Favoritos</a>
            <a href="?page=messages"><i class="fas fa-comments" style="width:16px;text-align:center;"></i> Mensajes</a>
            <?php if ($isAdmin): ?>
            <div style="border-top:1px solid var(--border);margin:4px 0;"></div>
            <a href="admin/" style="color:var(--red)!important;"><i class="fas fa-shield-alt" style="width:16px;text-align:center;"></i> Admin Panel</a>
            <?php endif; ?>
            <div style="border-top:1px solid var(--border);margin:4px 0;"></div>
            <a href="?action=logout"><i class="fas fa-sign-out-alt" style="width:16px;text-align:center;"></i> Cerrar sesión</a>
          </div>
        </div>
      <?php else: ?>
        <a href="?page=login" class="btn btn-ghost btn-sm">Entrar</a>
        <a href="?page=register" class="btn btn-red btn-sm">Registrarse</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Flash messages -->
<?php foreach (getFlash() as $f): ?>
<div style="background:var(--bg2);padding:8px 0;border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="alert alert-<?= Security::e($f['type']) ?>"><?= Security::e($f['msg']) ?></div>
  </div>
</div>
<?php endforeach; ?>

<!-- Page content starts here -->
