// ═══════════════════════════════════════════
//  REDLINECREW - Enhanced JavaScript v1.1
//  Appended to main.js functionality
// ═══════════════════════════════════════════

// ── Live notification polling ─────────────
(function () {
  const notifBell = document.querySelector('a[href*="notifications"]');
  const msgIcon   = document.querySelector('a[href*="messages"]');
  if (!notifBell && !msgIcon) return; // Not logged in

  function pollCounts() {
    fetch('api/notifications.php?action=count')
      .then(r => r.json())
      .then(data => {
        if (!data.ok) return;
        // Update notification badge
        const nb = notifBell?.querySelector('.badge-count');
        if (data.notifications > 0) {
          if (nb) { nb.textContent = data.notifications; nb.style.display = ''; }
          else if (notifBell) {
            const span = document.createElement('span');
            span.className = 'badge-count';
            span.textContent = data.notifications;
            notifBell.appendChild(span);
          }
        } else if (nb) nb.style.display = 'none';
        // Update messages badge
        const mb = msgIcon?.querySelector('.badge-count');
        if (data.messages > 0) {
          if (mb) { mb.textContent = data.messages; mb.style.display = ''; }
          else if (msgIcon) {
            const span = document.createElement('span');
            span.className = 'badge-count';
            span.textContent = data.messages;
            msgIcon.appendChild(span);
          }
        } else if (mb) mb.style.display = 'none';
      })
      .catch(() => {});
  }

  // Poll every 60 seconds
  setInterval(pollCounts, 60000);
})();

// ── Smooth scroll to top button ──────────
(function () {
  const btn = document.createElement('button');
  btn.id = 'scroll-top';
  btn.innerHTML = '↑';
  btn.style.cssText = 'position:fixed;bottom:100px;left:28px;width:40px;height:40px;background:var(--bg3);border:1px solid var(--border);border-radius:50%;color:var(--text-muted);font-size:16px;cursor:pointer;display:none;z-index:990;transition:.3s;';
  document.body.appendChild(btn);
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  window.addEventListener('scroll', () => {
    btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
    btn.style.alignItems = 'center';
    btn.style.justifyContent = 'center';
  });
})();

// ── Image zoom on product page ────────────
(function () {
  const mainImg = document.getElementById('main-img');
  if (!mainImg) return;
  mainImg.style.cursor = 'zoom-in';
  mainImg.addEventListener('click', function () {
    const src = mainImg.querySelector('img')?.src;
    if (!src) return;
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
    overlay.innerHTML = `<img src="${src}" style="max-width:92vw;max-height:92vh;object-fit:contain;border-radius:8px;box-shadow:0 0 60px rgba(0,0,0,.8);">`;
    overlay.addEventListener('click', () => overlay.remove());
    document.body.appendChild(overlay);
  });
})();

// ── Auto-refresh offers ticker ────────────
(function () {
  const ticker = document.getElementById('offers-ticker');
  if (!ticker) return;
  let tickerIndex = 0;
  let tickerItems = [];

  function updateTicker() {
    if (!tickerItems.length) return;
    const item = tickerItems[tickerIndex % tickerItems.length];
    ticker.style.opacity = '0';
    setTimeout(() => {
      const price = item.price ? ` <strong style="color:var(--gold)">${item.price}€</strong>` : '';
      ticker.innerHTML = `<span class="ticker-item">🔥 <a href="${item.url}" target="_blank" rel="noopener">${item.title}</a>${price} — <em style="color:var(--text-muted)">${item.source}</em></span>`;
      ticker.style.opacity = '1';
      ticker.style.transition = 'opacity .4s';
    }, 200);
    tickerIndex++;
  }

  fetch('api/offers.php?limit=20&format=ticker')
    .then(r => r.json())
    .then(d => {
      tickerItems = d.offers || [];
      if (tickerItems.length) {
        updateTicker();
        setInterval(updateTicker, 5000);
      }
    })
    .catch(() => {});
})();

// ── Price range slider feedback ───────────
(function () {
  const minInput = document.querySelector('input[name="min_price"]');
  const maxInput = document.querySelector('input[name="max_price"]');
  if (!minInput || !maxInput) return;
  function validate() {
    const min = parseInt(minInput.value) || 0;
    const max = parseInt(maxInput.value) || 0;
    if (max && min > max) {
      maxInput.style.borderColor = 'var(--red)';
    } else {
      maxInput.style.borderColor = '';
    }
  }
  minInput.addEventListener('input', validate);
  maxInput.addEventListener('input', validate);
})();

// ── Character counter enhancement ─────────
document.querySelectorAll('details').forEach(el => {
  el.addEventListener('toggle', function () {
    const arrow = el.querySelector('summary span:last-child');
    if (arrow) arrow.textContent = el.open ? '−' : '+';
  });
});

// ── Product card hover 3D tilt ────────────
document.querySelectorAll('.product-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top)  / rect.height - 0.5;
    card.style.transform = `translateY(-3px) rotateY(${x * 3}deg) rotateX(${-y * 3}deg)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = '';
  });
});

// ── Share button copy feedback ────────────
document.querySelectorAll('[onclick*="copyToClipboard"]').forEach(btn => {
  const orig = btn.innerHTML;
  const handler = btn.onclick;
  btn.addEventListener('click', () => {
    setTimeout(() => {
      btn.innerHTML = '✓ Copiado';
      btn.style.color = 'var(--green)';
      setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; }, 2000);
    }, 100);
  });
});
