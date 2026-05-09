/* GRATIS Async Notifications — toast + bell, zero dependencies, ~3KB */
(function() {
  'use strict';

  // ── Toast system ──────────────────────────────────────────────────────────
  const TOAST_TYPES = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };

  function toast(message, type = 'info', duration = 4000) {
    let container = document.getElementById('gratis-toasts');
    if (!container) {
      container = document.createElement('div');
      container.id = 'gratis-toasts';
      document.body.appendChild(container);
    }

    const el = document.createElement('div');
    el.className = `gratis-toast gratis-toast--${type}`;
    el.innerHTML = `<span class="gratis-toast-icon">${TOAST_TYPES[type] || 'ℹ'}</span>
      <span class="gratis-toast-msg">${message}</span>
      <button class="gratis-toast-close" aria-label="Close">×</button>`;

    el.querySelector('.gratis-toast-close').onclick = () => dismiss(el);
    container.appendChild(el);

    // Animate in
    requestAnimationFrame(() => el.classList.add('gratis-toast--visible'));

    if (duration > 0) setTimeout(() => dismiss(el), duration);
    return el;
  }

  function dismiss(el) {
    el.classList.remove('gratis-toast--visible');
    el.addEventListener('transitionend', () => el.remove(), { once: true });
  }

  // ── Bell notification system ──────────────────────────────────────────────
  let bellCount = 0;
  const bellListeners = [];

  function initBell() {
    const bells = document.querySelectorAll('.gratis-bell');
    bells.forEach(bell => {
      const badge = bell.querySelector('.gratis-bell-badge');
      const panel = bell.querySelector('.gratis-bell-panel');
      if (!panel) return;

      bell.addEventListener('click', function(e) {
        e.stopPropagation();
        const open = panel.classList.toggle('gratis-bell-panel--open');
        if (open) {
          // Mark as read
          bellCount = 0;
          if (badge) badge.textContent = '';
          badge?.classList.remove('gratis-bell-badge--active');
        }
      });

      document.addEventListener('click', function(e) {
        if (!bell.contains(e.target)) panel.classList.remove('gratis-bell-panel--open');
      });
    });
  }

  function addNotification(message, type = 'info', link = null) {
    bellCount++;
    const bells = document.querySelectorAll('.gratis-bell');
    bells.forEach(bell => {
      const badge = bell.querySelector('.gratis-bell-badge');
      const list = bell.querySelector('.gratis-bell-list');
      if (badge) { badge.textContent = bellCount > 9 ? '9+' : bellCount; badge.classList.add('gratis-bell-badge--active'); }
      if (list) {
        const item = document.createElement('a');
        item.className = `gratis-bell-item gratis-bell-item--${type}`;
        item.href = link || '#';
        item.innerHTML = `<span class="gratis-bell-dot"></span><span>${message}</span><time>${new Date().toLocaleTimeString()}</time>`;
        list.prepend(item);
        // Keep max 10
        while (list.children.length > 10) list.lastChild.remove();
      }
    });
    // Also show toast
    toast(message, type, 3000);
  }

  // ── WooCommerce cart events ───────────────────────────────────────────────
  document.addEventListener('added_to_cart', function(e) {
    addNotification('Item added to cart!', 'success', '/cart');
  });

  // ── Expose API ────────────────────────────────────────────────────────────
  window.GRATIS = window.GRATIS || {};
  window.GRATIS.toast = toast;
  window.GRATIS.notify = addNotification;

  // Demo: show a welcome toast after 2s on first visit
  if (!localStorage.getItem('gratis_welcomed')) {
    setTimeout(() => {
      toast('Welcome to GRATIS — the free premium theme! 🎉', 'info', 4000);
      localStorage.setItem('gratis_welcomed', '1');
    }, 2000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBell);
  } else {
    initBell();
  }
})();
