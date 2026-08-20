document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.admin-hamburger');
  const sidebar = document.querySelector('.admin-sidebar');
  const overlay = document.querySelector('.admin-overlay');

  hamburger?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay?.classList.toggle('open');
  });
  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
  });

  document.querySelectorAll('.table-search').forEach(input => {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.target.closest('form')?.submit();
      }
    });
  });
});
