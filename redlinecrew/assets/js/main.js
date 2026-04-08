// ═══════════════════════════════════════════
//  REDLINECREW - Main JavaScript
// ═══════════════════════════════════════════

const RLC = {

  // ── Init ─────────────────────────────────
  init() {
    this.initBot();
    this.initSearch();
    this.initFavorites();
    this.initImagePreviews();
    this.initTabs();
    this.initModals();
    this.initDropdowns();
    this.initOffersTicker();
    this.initLazyImages();
    this.initForms();
    this.initToasts();
    console.log('%c🏍 REDLINECREW', 'color:#e8192c;font-size:20px;font-weight:900;');
  },

  // ── Toast Notifications ───────────────────
  initToasts() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
    document.body.appendChild(container);
  },

  toast(msg, type = 'info', duration = 4000) {
    const colors = { success:'#00d68f', error:'#e8192c', info:'#2979ff', warning:'#f5a623' };
    const icons  = { success:'✓', error:'✕', info:'ℹ', warning:'⚠' };
    const t = document.createElement('div');
    t.style.cssText = `background:#16161e;border:1px solid ${colors[type]};border-left:3px solid ${colors[type]};color:#e8e8f0;padding:12px 16px;border-radius:8px;font-size:14px;min-width:280px;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,0.5);animation:fadeIn .3s ease;`;
    t.innerHTML = `<span style="color:${colors[type]};font-size:16px">${icons[type]}</span>${msg}`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(20px)'; t.style.transition = '.3s'; setTimeout(() => t.remove(), 300); }, duration);
  },

  // ── Bot ───────────────────────────────────
  botOpen: false,

  initBot() {
    const toggle = document.getElementById('bot-toggle');
    const win    = document.getElementById('bot-window');
    const input  = document.getElementById('bot-input');
    const sendBtn = document.getElementById('bot-send');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      this.botOpen = !this.botOpen;
      toggle.classList.toggle('open', this.botOpen);
      win.classList.toggle('open', this.botOpen);
      if (this.botOpen && !win.dataset.loaded) {
        this.botAddMessage('bot', '¡Hola! 🏍️ Soy **RedBot**. ¿En qué te puedo ayudar?');
        win.dataset.loaded = '1';
      }
      if (this.botOpen && input) input.focus();
    });

    if (input) {
      input.addEventListener('keydown', e => { if (e.key === 'Enter') this.botSend(); });
    }
    if (sendBtn) sendBtn.addEventListener('click', () => this.botSend());
  },

  botSend() {
    const input = document.getElementById('bot-input');
    const msg = input?.value?.trim();
    if (!msg) return;
    input.value = '';
    this.botAddMessage('user', msg);
    this.botTyping(true);
    fetch('api/bot.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg, csrf: document.querySelector('meta[name=csrf]')?.content })
    })
    .then(r => r.json())
    .then(d => {
      this.botTyping(false);
      this.botAddMessage('bot', d.response || 'Lo siento, ocurrió un error.');
    })
    .catch(() => {
      this.botTyping(false);
      this.botAddMessage('bot', 'Error de conexión. Inténtalo de nuevo.');
    });
  },

  botAddMessage(role, text) {
    const msgs = document.getElementById('bot-messages');
    if (!msgs) return;
    const div = document.createElement('div');
    div.className = `bot-msg ${role}`;
    // Simple markdown: **bold**, *italic*, links
    const html = text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
      .replace(/\n/g, '<br>');
    div.innerHTML = `<div class="bubble">${html}</div>`;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  },

  botTyping(show) {
    const msgs = document.getElementById('bot-messages');
    if (!msgs) return;
    const existing = msgs.querySelector('.typing-msg');
    if (show && !existing) {
      const d = document.createElement('div');
      d.className = 'bot-msg typing-msg';
      d.innerHTML = '<div class="bubble"><div class="typing"><span></span><span></span><span></span></div></div>';
      msgs.appendChild(d);
      msgs.scrollTop = msgs.scrollHeight;
    } else if (!show && existing) {
      existing.remove();
    }
  },

  // ── Search ───────────────────────────────
  searchTimeout: null,
  initSearch() {
    const input = document.getElementById('main-search');
    const results = document.getElementById('search-dropdown');
    if (!input || !results) return;

    input.addEventListener('input', () => {
      clearTimeout(this.searchTimeout);
      const q = input.value.trim();
      if (q.length < 2) { results.style.display = 'none'; return; }
      this.searchTimeout = setTimeout(() => this.doSearch(q, results), 300);
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.location.href = `?page=search&q=${encodeURIComponent(input.value)}`;
      }
    });

    document.addEventListener('click', e => {
      if (!input.contains(e.target)) results.style.display = 'none';
    });
  },

  doSearch(q, results) {
    fetch(`api/search.php?q=${encodeURIComponent(q)}&limit=5`)
      .then(r => r.json())
      .then(data => {
        if (!data.results?.length) { results.style.display = 'none'; return; }
        results.innerHTML = data.results.map(p => `
          <a href="?page=product&id=${p.id}" class="search-result-item">
            <span class="search-cat">${p.category}</span>
            <strong>${p.title}</strong>
            <span class="search-price">${p.price ? p.price + ' €' : 'Consultar'}</span>
          </a>
        `).join('');
        results.style.display = 'block';
      })
      .catch(() => { results.style.display = 'none'; });
  },

  // ── Favorites ────────────────────────────
  initFavorites() {
    document.querySelectorAll('.fav-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const id = btn.dataset.id;
        if (!id) return;
        fetch('api/favorites.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: id, csrf: document.querySelector('meta[name=csrf]')?.content })
        })
        .then(r => r.json())
        .then(d => {
          if (d.ok) {
            btn.classList.toggle('active', d.favorited);
            btn.innerHTML = d.favorited ? '♥' : '♡';
            this.toast(d.favorited ? 'Añadido a favoritos' : 'Eliminado de favoritos', 'success');
          } else if (d.login) {
            this.toast('Inicia sesión para guardar favoritos', 'info');
          }
        });
      });
    });
  },

  // ── Image Preview (upload) ────────────────
  initImagePreviews() {
    const input = document.getElementById('product-images');
    const preview = document.getElementById('image-preview');
    if (!input || !preview) return;

    const zone = document.querySelector('.upload-zone');
    if (zone) {
      zone.addEventListener('click', () => input.click());
      zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
      zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        const dt = new DataTransfer();
        [...(input.files || []), ...e.dataTransfer.files].forEach(f => dt.items.add(f));
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
      });
    }

    input.addEventListener('change', () => {
      preview.innerHTML = '';
      [...input.files].slice(0, 6).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
          const div = document.createElement('div');
          div.className = 'img-thumb';
          div.style.cssText = 'position:relative;width:100px;height:100px;border-radius:8px;overflow:hidden;border:1px solid var(--border);display:inline-block;margin:4px;';
          div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;"><button onclick="this.parentElement.remove()" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.7);border:none;color:#fff;border-radius:50%;width:20px;height:20px;font-size:12px;cursor:pointer;">✕</button>`;
          preview.appendChild(div);
        };
        reader.readAsDataURL(file);
      });
    });
  },

  // ── Tabs ─────────────────────────────────
  initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const group = btn.closest('.tabs').dataset.group || 'default';
        document.querySelectorAll(`[data-tab-group="${group}"]`).forEach(p => p.classList.add('hidden'));
        document.querySelectorAll(`.tabs[data-group="${group}"] .tab-btn`).forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const target = document.getElementById(btn.dataset.tab);
        if (target) target.classList.remove('hidden');
      });
    });
  },

  // ── Modals ───────────────────────────────
  initModals() {
    document.querySelectorAll('[data-modal]').forEach(btn => {
      btn.addEventListener('click', () => {
        const modal = document.getElementById(btn.dataset.modal);
        if (modal) modal.classList.add('open');
      });
    });
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('open');
      });
    });
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.closest('.modal-overlay').classList.remove('open');
      });
    });
  },

  // ── Dropdowns ────────────────────────────
  initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach(trigger => {
      const menu = document.getElementById(trigger.dataset.dropdown);
      if (!menu) return;
      trigger.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('open');
      });
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
    });
  },

  // ── Offers Ticker ────────────────────────
  initOffersTicker() {
    const ticker = document.getElementById('offers-ticker');
    if (!ticker) return;
    fetch('api/offers.php?limit=10&format=ticker')
      .then(r => r.json())
      .then(data => {
        if (!data.offers?.length) return;
        ticker.innerHTML = data.offers.map(o =>
          `<span class="ticker-item"><span class="text-gold">🔥</span> <a href="${o.url}" target="_blank" rel="noopener">${o.title}</a> ${o.price ? '<strong>' + o.price + '€</strong>' : ''}</span>`
        ).join('<span class="ticker-sep">•</span>');
      });
  },

  // ── Lazy Images ──────────────────────────
  initLazyImages() {
    if ('IntersectionObserver' in window) {
      const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            obs.unobserve(img);
          }
        });
      }, { rootMargin: '200px' });
      document.querySelectorAll('img[data-src]').forEach(img => obs.observe(img));
    }
  },

  // ── Form enhancements ───────────────────
  initForms() {
    // Price formatter
    document.querySelectorAll('input[data-price]').forEach(input => {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/[^0-9.,]/g, '');
      });
    });

    // Char counters
    document.querySelectorAll('[data-maxlength]').forEach(el => {
      const max = parseInt(el.dataset.maxlength);
      const counter = document.createElement('small');
      counter.style.cssText = 'color:var(--text-muted);float:right;margin-top:4px;';
      el.parentNode.appendChild(counter);
      const update = () => { counter.textContent = `${el.value.length}/${max}`; };
      el.addEventListener('input', update);
      update();
    });

    // Confirm dangerous actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
      });
    });
  },

  // ── Utility ─────────────────────────────
  formatPrice(num) {
    return new Intl.NumberFormat('es-ES', { style:'currency', currency:'EUR' }).format(num);
  },

  copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => this.toast('Copiado al portapapeles', 'success'));
  },

  // ── Admin functions ──────────────────────
  updateProductStatus(id, status) {
    if (!confirm(`¿Cambiar estado a "${status}"?`)) return;
    fetch('admin/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_product_status', id, status, csrf: document.querySelector('meta[name=csrf]')?.content })
    })
    .then(r => r.json())
    .then(d => {
      if (d.ok) { this.toast('Estado actualizado', 'success'); setTimeout(() => location.reload(), 800); }
      else this.toast(d.msg || 'Error', 'error');
    });
  },

  scrapeOffers() {
    const btn = document.getElementById('scrape-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Actualizando...'; }
    fetch('admin/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'scrape_offers', csrf: document.querySelector('meta[name=csrf]')?.content })
    })
    .then(r => r.json())
    .then(d => {
      if (btn) { btn.disabled = false; btn.textContent = '🔄 Actualizar Ofertas'; }
      if (d.ok) this.toast(`✅ ${d.stats.added} nuevas ofertas importadas`, 'success');
      else this.toast('Error al actualizar', 'error');
    });
  },

  deleteUser(id) {
    if (!confirm('¿Eliminar este usuario permanentemente?')) return;
    fetch('admin/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_user', id, csrf: document.querySelector('meta[name=csrf]')?.content })
    })
    .then(r => r.json())
    .then(d => {
      if (d.ok) { this.toast('Usuario eliminado', 'success'); setTimeout(() => location.reload(), 800); }
      else this.toast(d.msg || 'Error', 'error');
    });
  },
};

document.addEventListener('DOMContentLoaded', () => RLC.init());
