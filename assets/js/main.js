document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  const overlay = document.querySelector('.nav-overlay');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      overlay?.classList.toggle('open');
      hamburger.classList.toggle('active');
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      navLinks.classList.remove('open');
      overlay.classList.remove('open');
      hamburger?.classList.remove('active');
    });
  }
  document.querySelectorAll('.nav-links a').forEach(a => {
    a.addEventListener('click', () => {
      navLinks?.classList.remove('open');
      overlay?.classList.remove('open');
    });
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
