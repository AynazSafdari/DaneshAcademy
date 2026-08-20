document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  const overlay = document.querySelector('.nav-overlay');

  function openNav() {
    if (!navLinks) return;
    navLinks.classList.add('open');
    overlay && overlay.classList.add('open');
    hamburger && hamburger.classList.add('hidden');
    document.body.classList.add('nav-locked');
  }

  function closeNav() {
    if (!navLinks) return;
    navLinks.classList.remove('open');
    overlay && overlay.classList.remove('open');
    hamburger && hamburger.classList.remove('hidden');
    document.body.classList.remove('nav-locked');
  }

  if (hamburger) {
    hamburger.addEventListener('click', openNav);
  }

  if (overlay) {
    overlay.addEventListener('click', closeNav);
  }


  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('.nav-close');
    if (closeBtn) {
      e.preventDefault();
      closeNav();
      return;
    }
    const navLink = e.target.closest('.nav-links a');
    if (navLink) {
      closeNav();
    }
  });

  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('in-view');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('in-view'));
  }


  const toastEl = document.querySelector('[data-flash-message]');
  if (toastEl) {
    const msg = toastEl.dataset.flashMessage;
    const isDanger = toastEl.dataset.flashType === 'danger';
    showToast(msg, isDanger);
  }

  initSliders();
  initBookmarks();
});


// slider
function initSliders() {
  document.querySelectorAll('.slider-track').forEach(track => {
    const id = track.id;
    const dotsWrap = document.querySelector(`[data-slider-dots="${id}"]`);
    const prevBtn = document.querySelector(`[data-slider-prev="${id}"]`);
    const nextBtn = document.querySelector(`[data-slider-next="${id}"]`);
    const items = Array.from(track.children);
    if (!items.length) return;

    if (dotsWrap) {
      dotsWrap.innerHTML = items.map((_, i) => `<span class="dot${i === 0 ? ' active' : ''}"></span>`).join('');
    }
    const dots = dotsWrap ? Array.from(dotsWrap.children) : [];

    function updateDots() {
      if (!dots.length) return;
      const trackRect = track.getBoundingClientRect();
      let closestIdx = 0;
      let closestDist = Infinity;
      items.forEach((item, i) => {
        const itemRect = item.getBoundingClientRect();
        const dist = Math.abs(itemRect.right - trackRect.right);
        if (dist < closestDist) { closestDist = dist; closestIdx = i; }
      });
      dots.forEach((d, i) => d.classList.toggle('active', i === closestIdx));
    }

    function scrollByCard(direction) {
      const card = items[0];
      const gap = parseFloat(getComputedStyle(track).gap || '16');
      const cardWidth = card.getBoundingClientRect().width + gap;
      track.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
    }


    prevBtn?.addEventListener('click', () => scrollByCard(1));
    nextBtn?.addEventListener('click', () => scrollByCard(-1));

    track.addEventListener('scroll', () => {
      clearTimeout(track._scrollTimer);
      track._scrollTimer = setTimeout(updateDots, 80);
    });

    updateDots();
  });
}

function showToast(msg, isDanger) {
  let toast = document.querySelector('.toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.toggle('toast-danger', !!isDanger);
  toast.classList.add('show');
  clearTimeout(toast._t);
  toast._t = setTimeout(() => toast.classList.remove('show'), 2600);
}

// bookmark
function initBookmarks() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.bookmark-btn');
    if (!btn) return;

    e.preventDefault();
    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';

    const contentType = btn.dataset.contentType;
    const contentId = btn.dataset.contentId;

    try {
      const res = await fetch('bookmark-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `type=${encodeURIComponent(contentType)}&id=${encodeURIComponent(contentId)}`
      });
      const data = await res.json();

      if (!data.ok) {
        if (data.requiresAuth) {
          showToast(data.message || 'برای نشان‌کردن ابتدا ثبت‌نام کنید.', true);
          const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
          setTimeout(() => {
            window.location.href = `signup.php?redirect=${returnUrl}`;
          }, 900);
        } else {
          showToast(data.message || 'خطایی رخ داد.', true);
        }
        return;
      }

      const isBookmarked = data.bookmarked;
      btn.setAttribute('data-bookmarked', isBookmarked ? '1' : '0');

      const icon = btn.querySelector('.bookmark-icon');
      const label = btn.querySelector('.bookmark-label');
      if (icon) {
        icon.classList.toggle('fa-solid', isBookmarked);
        icon.classList.toggle('fa-regular', !isBookmarked);
      }
      if (label) {
        const isInline = btn.classList.contains('u-inline-bookmark');
        if (isInline) {
          label.textContent = isBookmarked ? 'نشان‌شده' : 'نشان‌کردن';
        } else {
          label.textContent = isBookmarked ? 'نشان‌شده — حذف از نشان‌ها' : 'نشان‌کردن این دوره';
        }
      }

      showToast(isBookmarked ? 'به نشان‌های شما اضافه شد.' : 'از نشان‌های شما حذف شد.');
    } catch (err) {
      showToast('خطا در ارتباط با سرور.', true);
    } finally {
      btn.dataset.loading = '0';
    }
  });
}
