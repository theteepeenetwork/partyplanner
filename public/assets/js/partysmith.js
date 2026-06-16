/* Partysmith front-of-house interactions.
   Ported from the design handoff's home.js (the FontAwesome->SVG icon shim is
   intentionally dropped — Font Awesome is already loaded sitewide).
   Every block is guarded so it is a no-op on pages without the elements. */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* Sticky-nav shadow (only if a design .nav#nav is present). */
    var nav = document.getElementById('nav');
    if (nav && nav.classList.contains('nav')) {
      var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 12); };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }

    /* Hero occasion tabs — single select; the "+ more" tab is inert. */
    var tabs = document.querySelectorAll('.search-tabs .tab');
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        if (t.dataset.occasion === 'more') return;
        tabs.forEach(function (x) { if (x.dataset.occasion !== 'more') x.classList.remove('on'); });
        t.classList.add('on');
        var hidden = document.getElementById('ps-occasion-input');
        if (hidden) hidden.value = t.dataset.occasion || '';
      });
    });

    /* FAQ accordion — one item open at a time, animated max-height. */
    var items = document.querySelectorAll('.faq-item');
    var setOpen = function (item, open) {
      var a = item.querySelector('.faq-a');
      item.classList.toggle('open', open);
      if (a) a.style.maxHeight = open ? a.scrollHeight + 'px' : '0px';
    };
    items.forEach(function (item) {
      var q = item.querySelector('.faq-q');
      if (!q) return;
      q.addEventListener('click', function () {
        var isOpen = item.classList.contains('open');
        items.forEach(function (o) { setOpen(o, false); });
        setOpen(item, !isOpen);
      });
      if (item.classList.contains('open')) setOpen(item, true);
    });
    window.addEventListener('resize', function () {
      var open = document.querySelector('.faq-item.open');
      if (open) { var a = open.querySelector('.faq-a'); if (a) a.style.maxHeight = a.scrollHeight + 'px'; }
    });

    /* Single-select pill groups (browse filters). */
    document.querySelectorAll('.pillset').forEach(function (set) {
      set.addEventListener('click', function (e) {
        var p = e.target.closest('.pill');
        if (!p || !set.contains(p)) return;
        set.querySelectorAll('.pill').forEach(function (x) { x.classList.remove('on'); });
        p.classList.add('on');
      });
    });

    /* Toggle switches (browse "good to know"). */
    document.querySelectorAll('.switch').forEach(function (s) {
      s.addEventListener('click', function () {
        var on = s.classList.toggle('on');
        s.setAttribute('aria-checked', on ? 'true' : 'false');
      });
    });

    /* Favourite hearts (browse results, profile actions). UI-only. */
    document.querySelectorAll('.result-fav, .icon-btn[data-fav]').forEach(function (b) {
      b.addEventListener('click', function (e) { e.preventDefault(); b.classList.toggle('on'); });
    });

  });
})();
