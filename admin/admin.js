/* ===================================================================
   آکادمی دانش — رفتار سایدبار پنل مدیریت
   احراز هویت و داده‌ها اکنون کاملاً سمت سرور (PHP) هستند
   =================================================================== */

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

  // ارسال خودکار فرم جستجوی جدول هنگام Enter (در حال حاضر input type=text با submit دستی هم کار می‌کند)
  document.querySelectorAll('.table-search').forEach(input => {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.target.closest('form')?.submit();
      }
    });
  });
});
