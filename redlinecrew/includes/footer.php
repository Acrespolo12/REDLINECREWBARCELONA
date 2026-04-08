<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Footer Template
// ═══════════════════════════════════════════
?>
<!-- ── FOOTER ──────────────────────────────── -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div>
        <div class="footer-logo">RED<span style="color:var(--red)">LINE</span>CREW</div>
        <p class="footer-desc">La comunidad motera definitiva. Compra, vende y descubre las mejores ofertas en motos y equipamiento.</p>
        <div style="display:flex;gap:12px;margin-top:20px;">
          <a href="#" style="width:36px;height:36px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;"><i class="fab fa-instagram"></i></a>
          <a href="#" style="width:36px;height:36px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;"><i class="fab fa-facebook"></i></a>
          <a href="#" style="width:36px;height:36px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;"><i class="fab fa-youtube"></i></a>
          <a href="#" style="width:36px;height:36px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>

      <!-- Marketplace -->
      <div class="footer-links">
        <h5>Marketplace</h5>
        <ul>
          <li><a href="?page=products">Todos los productos</a></li>
          <li><a href="?page=categories">Categorías</a></li>
          <li><a href="?page=offers">Ofertas del día</a></li>
          <li><a href="?page=sell">Vender mi producto</a></li>
          <li><a href="?page=search">Búsqueda avanzada</a></li>
        </ul>
      </div>

      <!-- Mi cuenta -->
      <div class="footer-links">
        <h5>Mi Cuenta</h5>
        <ul>
          <li><a href="?page=register">Registrarse</a></li>
          <li><a href="?page=login">Iniciar sesión</a></li>
          <li><a href="?page=profile">Mi perfil</a></li>
          <li><a href="?page=favorites">Favoritos</a></li>
          <li><a href="?page=messages">Mensajes</a></li>
        </ul>
      </div>

      <!-- Info -->
      <div class="footer-links">
        <h5>Información</h5>
        <ul>
          <li><a href="?page=about">Sobre nosotros</a></li>
          <li><a href="?page=contact">Contacto</a></li>
          <li><a href="?page=privacy">Privacidad</a></li>
          <li><a href="?page=terms">Términos de uso</a></li>
          <li><a href="?page=faq">FAQ</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> REDLINECREW — Todos los derechos reservados</span>
      <span style="display:flex;align-items:center;gap:8px;">
        <span class="live-dot"></span>
        Ofertas actualizadas automáticamente
      </span>
    </div>
  </div>
</footer>

<!-- ── BOT ─────────────────────────────────── -->
<button id="bot-toggle" title="RedBot - Asistente" aria-label="Abrir asistente">
  <span id="bot-icon">🏍️</span>
</button>

<div id="bot-window" role="dialog" aria-label="Chat RedBot">
  <div class="bot-header">
    <div class="bot-avatar">🤖</div>
    <div class="bot-header-info">
      <h4>RedBot</h4>
      <span><span class="live-dot" style="width:6px;height:6px;"></span> En línea</span>
    </div>
    <button onclick="document.getElementById('bot-toggle').click()" style="margin-left:auto;background:rgba(255,255,255,.2);border:none;color:#fff;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:14px;">✕</button>
  </div>
  <div id="bot-messages" class="bot-messages"></div>
  <div class="bot-input-area">
    <input type="text" id="bot-input" placeholder="Escribe tu pregunta..." autocomplete="off" maxlength="500">
    <button id="bot-send" aria-label="Enviar"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<!-- ── SCRIPTS ─────────────────────────────── -->
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/enhancements.js') ?>"></script>
</body>
</html>
