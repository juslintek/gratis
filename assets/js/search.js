/* GRATIS Live Search — zero dependencies, ~2KB */
(function() {
  'use strict';

  function init() {
    const inputs = document.querySelectorAll('.gratis-search-input');
    inputs.forEach(attachSearch);
  }

  function attachSearch(input) {
    const wrap = input.closest('.gratis-search-wrap');
    if (!wrap) return;

    let results = wrap.querySelector('.gratis-search-results');
    if (!results) {
      results = document.createElement('div');
      results.className = 'gratis-search-results';
      wrap.appendChild(results);
    }

    let timer, controller;

    input.addEventListener('input', function() {
      const q = this.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { results.hidden = true; return; }
      timer = setTimeout(() => search(q, results), 200);
    });

    input.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { results.hidden = true; this.blur(); }
    });

    document.addEventListener('click', function(e) {
      if (!wrap.contains(e.target)) results.hidden = true;
    });
  }

  async function search(q, results) {
    if (typeof AbortController !== 'undefined') {
      if (window._gratisSearchCtrl) window._gratisSearchCtrl.abort();
      window._gratisSearchCtrl = new AbortController();
    }

    results.innerHTML = '<div class="gratis-search-loading">Searching…</div>';
    results.hidden = false;

    try {
      const url = (window.gratisSearch?.restUrl || '/wp-json/wp/v2/') +
        'search?search=' + encodeURIComponent(q) + '&per_page=6&_fields=id,title,url,subtype';
      const r = await fetch(url, {
        signal: window._gratisSearchCtrl?.signal,
        headers: { 'X-WP-Nonce': window.gratisSearch?.nonce || '' }
      });
      const data = await r.json();

      if (!data.length) {
        results.innerHTML = '<div class="gratis-search-empty">No results for "<strong>' + q + '</strong>"</div>';
        return;
      }

      results.innerHTML = data.map(item => `
        <a href="${item.url}" class="gratis-search-item">
          <span class="gratis-search-type">${item.subtype || 'page'}</span>
          <span class="gratis-search-title">${item.title.rendered || item.title}</span>
        </a>
      `).join('');
    } catch(e) {
      if (e.name !== 'AbortError') results.innerHTML = '<div class="gratis-search-empty">Search unavailable</div>';
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
