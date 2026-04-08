<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Admin Panel
// ═══════════════════════════════════════════

require_once __DIR__ . '/../includes/bootstrap.php';
Auth::requireAdmin();

$db  = Database::getInstance();
$tab = preg_replace('/[^a-z_]/', '', $_GET['tab'] ?? 'dashboard');
$pageTitle = 'Panel Admin';

// Stats
$stats = [
    'users'         => $db->fetch("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
    'products_all'  => $db->fetch("SELECT COUNT(*) as c FROM products")['c'] ?? 0,
    'products_pend' => $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='pending'")['c'] ?? 0,
    'products_act'  => $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c'] ?? 0,
    'offers'        => $db->fetch("SELECT COUNT(*) as c FROM offers")['c'] ?? 0,
    'messages'      => $db->fetch("SELECT COUNT(*) as c FROM messages")['c'] ?? 0,
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
  <!-- Admin Sidebar -->
  <div class="admin-sidebar">
    <div style="padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:8px;">
      <div style="font-family:var(--font-head);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:4px;">Admin Panel</div>
      <div style="font-size:13px;color:var(--text-muted);">Bienvenido, <?= Security::e($_SESSION['user_name']) ?></div>
    </div>
    <?php
    $navItems = [
      ['dashboard','fas fa-chart-bar','Dashboard'],
      ['products', 'fas fa-box-open','Productos'],
      ['pending',  'fas fa-clock','Pendientes', $stats['products_pend']],
      ['users',    'fas fa-users','Usuarios'],
      ['offers',   'fas fa-bolt','Ofertas'],
      ['messages', 'fas fa-comments','Mensajes'],
      ['settings', 'fas fa-cog','Configuración'],
      ['logs',     'fas fa-list-alt','Actividad'],
    ];
    foreach ($navItems as $item): ?>
    <a href="admin/?tab=<?= $item[0] ?>" class="admin-nav-item <?= $tab===$item[0]?'active':'' ?>">
      <i class="<?= $item[1] ?>" style="width:18px;text-align:center;"></i>
      <?= $item[2] ?>
      <?php if (!empty($item[3])): ?>
      <span style="margin-left:auto;background:var(--red);color:#fff;font-size:11px;padding:2px 7px;border-radius:10px;"><?= $item[3] ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
    <div style="border-top:1px solid var(--border);padding-top:8px;margin-top:8px;">
      <a href="../" class="admin-nav-item"><i class="fas fa-home" style="width:18px;text-align:center;"></i> Ver sitio</a>
    </div>
  </div>

  <!-- Admin Main -->
  <div class="admin-main">

  <?php if ($tab === 'dashboard'): ?>
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:28px;text-transform:uppercase;">Dashboard</h1>

    <!-- Stats grid -->
    <div class="grid-4" style="margin-bottom:32px;">
      <?php foreach ([
        ['🧑‍🤝‍🧑','Usuarios registrados', $stats['users'], 'var(--blue)'],
        ['📦','Productos totales', $stats['products_all'], 'var(--green)'],
        ['⏳','Pendientes revisión', $stats['products_pend'], 'var(--gold)'],
        ['⚡','Ofertas importadas', $stats['offers'], 'var(--red)'],
      ] as [$icon,$label,$val,$color]): ?>
      <div class="stat-card">
        <div class="stat-icon"><?= $icon ?></div>
        <div>
          <div class="stat-num" style="color:<?= $color ?>;"><?= number_format($val) ?></div>
          <div class="stat-label"><?= $label ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick actions -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px;">
      <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:16px;text-transform:uppercase;">Acciones rápidas</h3>
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <button id="scrape-btn" onclick="RLC.scrapeOffers()" class="btn btn-ghost">
          <i class="fas fa-sync-alt"></i> Actualizar Ofertas
        </button>
        <a href="admin/?tab=pending" class="btn btn-ghost" style="position:relative;">
          <i class="fas fa-clock"></i> Revisar pendientes
          <?php if ($stats['products_pend'] > 0): ?>
          <span style="position:absolute;top:-6px;right:-6px;background:var(--red);color:#fff;font-size:10px;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;"><?= $stats['products_pend'] ?></span>
          <?php endif; ?>
        </a>
        <a href="admin/?tab=users" class="btn btn-ghost"><i class="fas fa-user-plus"></i> Gestionar usuarios</a>
      </div>
    </div>

    <!-- Recent products -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px;">
      <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:16px;text-transform:uppercase;">Últimos productos</h3>
      <table class="table">
        <thead><tr><th>Producto</th><th>Usuario</th><th>Precio</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($db->fetchAll("SELECT p.*,u.username FROM products p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 8") as $p): ?>
        <tr>
          <td><a href="../?page=product&id=<?= $p['id'] ?>" style="font-weight:600;color:var(--text);"><?= Security::e(mb_substr($p['title'],0,40)) ?></a></td>
          <td style="color:var(--text-muted);"><?= Security::e($p['username']) ?></td>
          <td style="color:var(--red);font-weight:700;"><?= formatPrice($p['price']) ?></td>
          <td><?php
            $colors=['active'=>'var(--green)','pending'=>'var(--gold)','sold'=>'var(--blue)','rejected'=>'var(--red)'];
            $labels=['active'=>'Activo','pending'=>'Pendiente','sold'=>'Vendido','rejected'=>'Rechazado'];
          ?><span style="color:<?= $colors[$p['status']]??'var(--text-muted)' ?>;font-size:13px;">● <?= $labels[$p['status']]??$p['status'] ?></span></td>
          <td style="color:var(--text-muted);font-size:13px;"><?= timeAgo($p['created_at']) ?></td>
          <td>
            <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'active')" class="btn btn-sm" style="background:rgba(0,214,143,.15);color:var(--green);padding:4px 10px;">✓</button>
            <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'rejected')" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);padding:4px 10px;">✕</button>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php elseif ($tab === 'pending'): ?>
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:28px;text-transform:uppercase;">
      ⏳ Anuncios Pendientes
      <?php if ($stats['products_pend']): ?><span style="font-size:16px;background:var(--red);color:#fff;padding:4px 12px;border-radius:20px;vertical-align:middle;"><?= $stats['products_pend'] ?></span><?php endif; ?>
    </h1>
    <?php $pending = $db->fetchAll("SELECT p.*,u.username,u.email FROM products p JOIN users u ON p.user_id=u.id WHERE p.status='pending' ORDER BY p.created_at ASC"); ?>
    <?php if (empty($pending)): ?>
    <div style="text-align:center;padding:60px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
      <div style="font-size:48px;margin-bottom:12px;">✅</div>
      <h3 style="font-family:var(--font-head);font-size:22px;">Todo revisado — no hay pendientes</h3>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <?php foreach ($pending as $p):
        $images = json_decode($p['images'] ?? '[]', true);
        $img = !empty($images) ? UPLOAD_URL . $images[0] : null;
        $cat = PRODUCT_CATEGORIES[$p['category']] ?? ['name' => $p['category'], 'icon' => '🏍️'];
      ?>
      <div style="background:var(--surface);border:1px solid var(--gold);border-radius:var(--radius-lg);padding:20px;display:flex;gap:16px;align-items:flex-start;">
        <?php if ($img): ?><img src="<?= Security::e($img) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:var(--radius);flex-shrink:0;"><?php endif; ?>
        <div style="flex:1;">
          <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px;">
            <h3 style="font-size:16px;font-weight:700;"><?= Security::e($p['title']) ?></h3>
            <span style="background:var(--bg3);padding:2px 10px;border-radius:20px;font-size:12px;"><?= $cat['icon'] ?> <?= Security::e($cat['name']) ?></span>
          </div>
          <p style="color:var(--text-muted);font-size:13px;margin-bottom:8px;"><?= Security::e(mb_substr($p['description'],0,150)) ?>...</p>
          <div style="font-size:13px;color:var(--text-muted);display:flex;gap:16px;">
            <span>👤 <?= Security::e($p['username']) ?></span>
            <span>📧 <?= Security::e($p['email']) ?></span>
            <span>💰 <?= formatPrice($p['price']) ?></span>
            <span>📍 <?= Security::e($p['location'] ?: 'N/A') ?></span>
            <span>🕒 <?= timeAgo($p['created_at']) ?></span>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-direction:column;flex-shrink:0;">
          <a href="../?page=product&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" target="_blank">👁 Ver</a>
          <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'active')" class="btn btn-sm" style="background:rgba(0,214,143,.2);color:var(--green);">✓ Aprobar</button>
          <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'rejected')" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);">✕ Rechazar</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php elseif ($tab === 'users'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;text-transform:uppercase;">👥 Usuarios</h1>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <table class="table">
      <thead><tr><th>#</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Registro</th><th>Último acceso</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($db->fetchAll("SELECT * FROM users ORDER BY created_at DESC") as $u): ?>
      <tr>
        <td style="color:var(--text-muted);font-size:12px;">#<?= $u['id'] ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;color:#fff;font-size:13px;"><?= strtoupper(substr($u['username'],0,1)) ?></div>
            <div>
              <div style="font-weight:600;"><?= Security::e($u['username']) ?></div>
              <div style="font-size:11px;color:<?= $u['active']?'var(--green)':'var(--red)' ?>;"><?= $u['active']?'Activo':'Inactivo' ?></div>
            </div>
          </div>
        </td>
        <td style="color:var(--text-muted);font-size:13px;"><?= Security::e($u['email']) ?></td>
        <td><span style="background:<?= $u['role']==='admin'?'rgba(232,25,44,.2)':'var(--bg3)' ?>;color:<?= $u['role']==='admin'?'var(--red)':'var(--text-muted)' ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?= strtoupper($u['role']) ?></span></td>
        <td style="font-size:13px;color:var(--text-muted);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
        <td style="font-size:13px;color:var(--text-muted);"><?= $u['last_login'] ? timeAgo($u['last_login']) : '—' ?></td>
        <td>
          <?php if ($u['id'] != $_SESSION['user_id']): ?>
          <button onclick="RLC.deleteUser(<?= $u['id'] ?>)" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);padding:4px 10px;">🗑</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  <?php elseif ($tab === 'offers'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;text-transform:uppercase;">⚡ Ofertas</h1>
      <button id="scrape-btn" onclick="RLC.scrapeOffers()" class="btn btn-red">
        <i class="fas fa-download"></i> Importar ahora
      </button>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <table class="table">
      <thead><tr><th>Título</th><th>Fuente</th><th>Precio</th><th>Categoría</th><th>Fecha</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($db->fetchAll("SELECT * FROM offers ORDER BY pub_date DESC LIMIT 50") as $o): ?>
      <tr>
        <td style="max-width:280px;"><a href="<?= Security::e($o['url']) ?>" target="_blank" style="color:var(--text);font-size:13px;"><?= Security::e(mb_substr($o['title'],0,60)) ?></a></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= Security::e($o['source']) ?></td>
        <td style="color:var(--gold);font-weight:700;"><?= $o['price'] ? Security::e($o['price']).' €' : '—' ?></td>
        <td style="font-size:12px;"><?= isset(PRODUCT_CATEGORIES[$o['category']]) ? PRODUCT_CATEGORIES[$o['category']]['icon'].' '.PRODUCT_CATEGORIES[$o['category']]['name'] : '—' ?></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= $o['pub_date'] ? timeAgo($o['pub_date']) : '—' ?></td>
        <td><a href="<?= Security::e($o['url']) ?>" target="_blank" class="btn btn-ghost btn-sm" style="font-size:11px;"><i class="fas fa-external-link-alt"></i></a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  <?php elseif ($tab === 'settings'): ?>
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:28px;text-transform:uppercase;">⚙️ Configuración</h1>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
        if (Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            foreach (['site_name','site_description','offers_refresh_interval','maintenance_mode','registration_open','bot_enabled','bot_name'] as $key) {
                if (isset($_POST[$key])) {
                    $val = $_POST[$key];
                    $db->query("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?", [$key,$val,$val]);
                }
            }
            flash('success','Configuración guardada');
        }
    }
    $cfg = [];
    foreach ($db->fetchAll("SELECT * FROM settings") as $s) $cfg[$s['key']] = $s['value'];
    ?>
    <div class="card" style="padding:32px;max-width:600px;">
      <form method="POST">
        <input type="hidden" name="save_settings" value="1">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <?php foreach ([
          ['site_name','Nombre del sitio','text'],
          ['site_description','Descripción','text'],
          ['bot_name','Nombre del bot','text'],
          ['offers_refresh_interval','Intervalo actualización ofertas (segundos)','number'],
        ] as [$key,$label,$type]): ?>
        <div class="form-group">
          <label><?= $label ?></label>
          <input type="<?= $type ?>" name="<?= $key ?>" class="form-control" value="<?= Security::e($cfg[$key] ?? '') ?>">
        </div>
        <?php endforeach; ?>
        <div style="display:flex;gap:24px;margin-bottom:20px;">
          <?php foreach (['maintenance_mode'=>'Modo mantenimiento','registration_open'=>'Registro abierto','bot_enabled'=>'Bot habilitado'] as $key=>$label): ?>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
            <input type="checkbox" name="<?= $key ?>" value="1" <?= ($cfg[$key]??0)?'checked':'' ?> style="accent-color:var(--red);width:16px;height:16px;">
            <?= $label ?>
          </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-red">Guardar configuración</button>
      </form>
    </div>

  <?php elseif ($tab === 'logs'): ?>
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:28px;text-transform:uppercase;">📋 Actividad Reciente</h1>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <table class="table">
      <thead><tr><th>Acción</th><th>Usuario</th><th>Detalles</th><th>IP</th><th>Fecha</th></tr></thead>
      <tbody>
      <?php foreach ($db->fetchAll("SELECT l.*,u.username FROM activity_logs l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT 100") as $log): ?>
      <tr>
        <td><code style="font-size:12px;color:var(--red);"><?= Security::e($log['action']) ?></code></td>
        <td style="font-size:13px;"><?= Security::e($log['username'] ?? 'Sistema') ?></td>
        <td style="font-size:12px;color:var(--text-muted);max-width:300px;"><?= Security::e($log['details'] ?? '') ?></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= Security::e($log['ip'] ?? '') ?></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= timeAgo($log['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  <?php elseif ($tab === 'products'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;text-transform:uppercase;">📦 Todos los Productos</h1>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <table class="table">
      <thead><tr><th>Producto</th><th>Usuario</th><th>Cat</th><th>Precio</th><th>Estado</th><th>Vistas</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($db->fetchAll("SELECT p.*,u.username FROM products p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 100") as $p):
        $colors=['active'=>'var(--green)','pending'=>'var(--gold)','sold'=>'var(--blue)','rejected'=>'var(--red)'];
        $labels=['active'=>'Activo','pending'=>'Pendiente','sold'=>'Vendido','rejected'=>'Rechazado'];
      ?>
      <tr>
        <td><a href="../?page=product&id=<?= $p['id'] ?>" style="font-size:13px;color:var(--text);"><?= Security::e(mb_substr($p['title'],0,45)) ?></a></td>
        <td style="font-size:13px;color:var(--text-muted);"><?= Security::e($p['username']) ?></td>
        <td style="font-size:12px;"><?= PRODUCT_CATEGORIES[$p['category']]['icon'] ?? '🏍️' ?></td>
        <td style="color:var(--red);font-weight:700;font-size:13px;"><?= formatPrice($p['price']) ?></td>
        <td><span style="color:<?= $colors[$p['status']]??'var(--text-muted)' ?>;font-size:12px;">● <?= $labels[$p['status']]??$p['status'] ?></span></td>
        <td style="font-size:13px;color:var(--text-muted);"><?= $p['views'] ?></td>
        <td style="display:flex;gap:4px;">
          <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'active')" class="btn btn-sm" style="background:rgba(0,214,143,.15);color:var(--green);padding:3px 8px;font-size:11px;">✓</button>
          <button onclick="RLC.updateProductStatus(<?= $p['id'] ?>,'rejected')" class="btn btn-sm" style="background:rgba(232,25,44,.15);color:var(--red);padding:3px 8px;font-size:11px;">✕</button>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  <?php elseif ($tab === 'messages'): ?>
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:28px;text-transform:uppercase;">💬 Mensajes</h1>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
    <table class="table">
      <thead><tr><th>De</th><th>Para</th><th>Mensaje</th><th>Fecha</th></tr></thead>
      <tbody>
      <?php foreach ($db->fetchAll("SELECT m.*,s.username as from_name,r.username as to_name FROM messages m JOIN users s ON s.id=m.sender_id JOIN users r ON r.id=m.receiver_id ORDER BY m.created_at DESC LIMIT 50") as $m): ?>
      <tr>
        <td style="font-size:13px;"><?= Security::e($m['from_name']) ?></td>
        <td style="font-size:13px;"><?= Security::e($m['to_name']) ?></td>
        <td style="font-size:13px;color:var(--text-muted);max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= Security::e($m['content']) ?></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= timeAgo($m['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
