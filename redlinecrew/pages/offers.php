<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Offers Page
// ═══════════════════════════════════════════

$db = Database::getInstance();
$pageTitle = 'Ofertas Moteras';

$cat    = preg_replace('/[^a-z_]/', '', strtolower($_GET['cat'] ?? ''));
$source = trim($_GET['source'] ?? '');
$page_n = max(1, (int)($_GET['p'] ?? 1));
$perPage = 24;
$offset  = ($page_n - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($cat) { $where[] = 'category = ?'; $params[] = $cat; }
if ($source) { $where[] = 'source = ?'; $params[] = $source; }
$whereStr = implode(' AND ', $where);

$total = (int)$db->fetch("SELECT COUNT(*) as c FROM offers WHERE $whereStr", $params)['c'];
$totalPages = ceil($total / $perPage);
$offers = $db->fetchAll("SELECT * FROM offers WHERE $whereStr ORDER BY pub_date DESC LIMIT $perPage OFFSET $offset", $params);
$sources = $db->fetchAll("SELECT DISTINCT source, COUNT(*) as cnt FROM offers GROUP BY source ORDER BY cnt DESC");
$lastUpdate = $db->fetch("SELECT MAX(created_at) as t FROM offers")['t'] ?? null;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
  <!-- Header -->
  <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:16px;">
    <div>
      <h1 style="font-family:var(--font-head);font-size:42px;font-weight:900;text-transform:uppercase;color:var(--gold);">
        ⚡ Ofertas Moteras
      </h1>
      <p style="color:var(--text-muted);margin-top:4px;">
        <?= number_format($total) ?> ofertas de webs especializadas •
        <?php if ($lastUpdate): ?>
        Actualizado <?= timeAgo($lastUpdate) ?>
        <?php endif; ?>
        <span class="live-dot" style="margin-left:6px;"></span>
      </p>
    </div>
    <div style="display:flex;gap:12px;">
      <?php if (Auth::isAdmin()): ?>
      <button id="scrape-btn" onclick="RLC.scrapeOffers()" class="btn btn-ghost btn-sm">
        <i class="fas fa-sync-alt"></i> Actualizar Ofertas
      </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Source filter tabs -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
    <a href="?page=offers" class="btn btn-sm <?= !$source?'btn-red':'btn-ghost' ?>">Todas</a>
    <?php foreach ($sources as $s): ?>
    <a href="?page=offers&source=<?= urlencode($s['source']) ?>" class="btn btn-sm <?= $source===$s['source']?'btn-red':'btn-ghost' ?>">
      <?= Security::e($s['source']) ?>
      <span style="background:rgba(255,255,255,.15);padding:1px 6px;border-radius:8px;font-size:11px;margin-left:4px;"><?= $s['cnt'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Category filter -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:32px;">
    <a href="?page=offers<?= $source?"&source=".urlencode($source):'' ?>" class="<?= !$cat?'text-red':'' ?>" style="font-size:13px;color:var(--text-muted);padding:4px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:20px;">Todas</a>
    <?php foreach (PRODUCT_CATEGORIES as $slug => $c): ?>
    <a href="?page=offers&cat=<?= $slug ?><?= $source?"&source=".urlencode($source):'' ?>" style="font-size:13px;padding:4px 12px;background:<?= $cat===$slug?'rgba(232,25,44,.15)':'var(--bg3)' ?>;border:1px solid <?= $cat===$slug?'var(--border-red)':'var(--border)' ?>;border-radius:20px;color:<?= $cat===$slug?'var(--red)':'var(--text-muted)' ?>;">
      <?= $c['icon'] ?> <?= Security::e($c['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($offers)): ?>
  <div style="text-align:center;padding:80px 20px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
    <div style="font-size:56px;margin-bottom:16px;">⚡</div>
    <h3 style="font-family:var(--font-head);font-size:28px;margin-bottom:8px;">Sin ofertas todavía</h3>
    <p style="color:var(--text-muted);margin-bottom:24px;">Las ofertas se importan automáticamente de webs moteras especializadas.</p>
    <?php if (Auth::isAdmin()): ?>
    <button onclick="RLC.scrapeOffers()" class="btn btn-red">
      <i class="fas fa-download"></i> Importar ofertas ahora
    </button>
    <?php endif; ?>
  </div>
  <?php else: ?>

  <!-- Offers grid -->
  <div class="grid-3">
    <?php foreach ($offers as $o):
      $catInfo = PRODUCT_CATEGORIES[$o['category'] ?? ''] ?? null;
    ?>
    <div class="offer-card fade-in">
      <?php if ($o['image']): ?>
      <div class="offer-img">
        <img data-src="<?= Security::e($o['image']) ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="">
      </div>
      <?php else: ?>
      <div style="aspect-ratio:16/9;background:linear-gradient(135deg,var(--bg3),var(--bg4));display:flex;align-items:center;justify-content:center;font-size:40px;">
        <?= $catInfo ? $catInfo['icon'] : '🏍️' ?>
      </div>
      <?php endif; ?>

      <div class="offer-body">
        <div class="offer-source">
          <i class="fas fa-external-link-alt"></i> <?= Security::e($o['source']) ?>
          <?php if ($catInfo): ?>
          • <?= $catInfo['icon'] ?> <?= Security::e($catInfo['name']) ?>
          <?php endif; ?>
        </div>
        <h4><?= Security::e($o['title']) ?></h4>
        <?php if ($o['description']): ?>
        <p style="font-size:13px;color:var(--text-muted);margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
          <?= Security::e($o['description']) ?>
        </p>
        <?php endif; ?>
        <div class="offer-footer">
          <?php if ($o['price']): ?>
          <span class="offer-badge"><?= Security::e($o['price']) ?> €</span>
          <?php else: ?>
          <span style="font-size:12px;color:var(--text-muted);"><?= $o['pub_date'] ? timeAgo($o['pub_date']) : '' ?></span>
          <?php endif; ?>
          <a href="<?= Security::e($o['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-sm">
            Ver oferta <i class="fas fa-external-link-alt"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($i = max(1,$page_n-2); $i <= min($totalPages,$page_n+2); $i++): ?>
    <a href="?page=offers&cat=<?= $cat ?>&source=<?= urlencode($source) ?>&p=<?= $i ?>" class="page-btn <?= $i==$page_n?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Info box -->
  <div style="margin-top:48px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="font-size:40px;">🔄</div>
    <div>
      <h4 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:6px;">Actualización automática</h4>
      <p style="color:var(--text-muted);font-size:14px;">Las ofertas se recopilan automáticamente de Motofichas, SoloMoto, MotorRaider, Motociclismo, RevZilla y más. Se actualizan cada hora.</p>
    </div>
    <div style="margin-left:auto;text-align:right;">
      <div style="font-family:var(--font-head);font-size:24px;font-weight:900;color:var(--gold);"><?= count($sources) ?></div>
      <div style="font-size:13px;color:var(--text-muted);">Fuentes activas</div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
