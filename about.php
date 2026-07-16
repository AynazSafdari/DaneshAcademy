<?php
$page_title = 'درباره ما';
$base_url = '';
$asset_prefix = '';
$extra_css = ['about'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / درباره ما</div>
    <h1>درباره آکادمی دانش</h1>
    <p>داستان ما، ارزش‌هایی که به آن باور داریم و تیمی که این مسیر را می‌سازد.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="story-layout reveal">
      <div>
        <p class="eyebrow">داستان ما</p>
        <h2>یادگیری را در دسترس همه قرار می‌دهیم</h2>
        <p class="u-mt-1">آکادمی دانش در سال ۱۴۰۱ با یک ایده ساده شکل گرفت: یادگیری مهارت‌های کاربردی نباید پیچیده، گران یا محدود به جغرافیا باشد.</p>
        <p class="u-mt-1">امروز، آکادمی دانش به خانه‌ی هزاران دانشجو تبدیل شده که هرکدام داستان رشد خودشان را دارند.</p>
      </div>
      <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop" alt="تیم آکادمی دانش">
    </div>
  </div>
</section>

<section class="section u-bg-cream-soft">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">ارزش‌های ما</p><h2>آنچه به آن متعهد هستیم</h2></div>
    </div>
    <div class="value-grid">
      <div class="value-card reveal"><span class="ic">📌</span><h3>کیفیت بدون تعارف</h3><p>هر دوره قبل از انتشار، توسط تیم محتوایی ما بازبینی و تایید می‌شود.</p></div>
      <div class="value-card reveal delay-1"><span class="ic">🤝</span><h3>یادگیری همراه با حمایت</h3><p>دانشجویان ما هیچ‌وقت تنها نیستند؛ پشتیبانی همیشه در دسترس است.</p></div>
      <div class="value-card reveal delay-2"><span class="ic">🌱</span><h3>رشد مستمر محتوا</h3><p>محتوای دوره‌ها به‌طور منظم به‌روزرسانی می‌شود.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="stat-band reveal">
      <div class="stat"><b>۸۰+</b><span>دوره فعال</span></div>
      <div class="stat"><b>۱۸,۰۰۰+</b><span>دانشجو</span></div>
      <div class="stat"><b>۴۲</b><span>مدرس متخصص</span></div>
      <div class="stat"><b>۴.۸</b><span>میانگین رضایت</span></div>
    </div>
  </div>
</section>

<section class="section u-bg-cream-soft">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">اعضای تیم</p><h2>چه کسانی پشت آکادمی دانش هستند؟</h2></div>
    </div>
    <div class="team-grid">
      <div class="team-card reveal"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop" alt=""><h4>آرش کیانی</h4><span>هم‌بنیان‌گذار و مدیرعامل</span></div>
      <div class="team-card reveal delay-1"><img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" alt=""><h4>سارا محمودی</h4><span>مدیر محتوا و طراحی</span></div>
      <div class="team-card reveal delay-2"><img src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?q=80&w=300&auto=format&fit=crop" alt=""><h4>حسین رضوانی</h4><span>مدیر آموزش</span></div>
      <div class="team-card reveal delay-3"><img src="https://images.unsplash.com/photo-1580489944071-8a9c75e60341?q=80&w=300&auto=format&fit=crop" alt=""><h4>نگار صادقی</h4><span>مدیر ارتباط با دانشجویان</span></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
