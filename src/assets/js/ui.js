// Minimal UI JS (no business logic modified)
(function () {
  const nav = document.querySelector('.nav');
  const toggle = document.querySelector('[data-nav-toggle]');
  if (nav && toggle) {
    toggle.addEventListener('click', () => {
      nav.classList.toggle('open');
    });
  }

  // Simple dismissible alerts (if you add data-dismiss)
  document.querySelectorAll('[data-dismiss]').forEach(btn => {
    btn.addEventListener('click', e => {
      const targetSel = btn.getAttribute('data-dismiss');
      const target = targetSel ? document.querySelector(targetSel) : btn.closest('.alert');
      if (target) target.remove();
    })
  });
})();
