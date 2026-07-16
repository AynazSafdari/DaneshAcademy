<?php
$page_title = null;
$base_url = '';
$asset_prefix = '';
$extra_css = ['home'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
foreach ($categories as &$cat) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE category_id = ?");
    $stmt->execute([$cat['id']]);
    $cat['count'] = $stmt->fetchColumn();
}

$featured = $pdo->query("
    SELECT c.*, cat.name AS category_name
    FROM courses c
    JOIN categories cat ON cat.id = c.category_id
    WHERE c.featured = 1
    ORDER BY c.created_at DESC
    LIMIT 4
")->fetchAll();

$articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 3")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-grid">
    <div class="reveal in-view">
      <p class="eyebrow">پلتفرم یادگیری آنلاین</p>
      <h1>دفترچه یادگیری شما<br>برای <span class="underline">مهارت‌های امروز</span></h1>
      <p class="hero-lead">بیش از ۸۰ دوره تخصصی در برنامه‌نویسی، طراحی، زبان و کسب‌وکار؛ با مدرسانی که خودشان در صنعت فعالیت می‌کنند.</p>
      <div class="hero-cta">
        <a href="courses.php" class="btn btn-primary">مشاهده دوره‌ها</a>
        <a href="about.php" class="btn btn-outline">آشنایی با آکادمی دانش</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><b>۸۰+</b><span>دوره تخصصی</span></div>
        <div class="stat"><b>۱۸,۰۰۰+</b><span>دانشجو</span></div>
        <div class="stat"><b>۴.۸</b><span>میانگین رضایت</span></div>
      </div>
    </div>
    <div class="hero-visual reveal delay-1">
      <?php if (!empty($featured[0])): ?>
      <div class="stack-card main">
        <img src="<?= h($featured[0]['image']) ?>" alt="<?= h($featured[0]['title']) ?>">
        <div class="course-cat"><?= h($featured[0]['category_name']) ?></div>
        <strong class="u-color-pine"><?= h($featured[0]['title']) ?></strong>
        <div class="course-meta u-mt-0_6"><span class="star">★ <?= h($featured[0]['rating']) ?></span><span>· <?= number_format($featured[0]['students']) ?> دانشجو</span></div>
      </div>
      <?php endif; ?>
      <div class="stack-card float-1">
        <div class="progress-ring">
          <div class="ring"></div>
          <div><strong class="u-text-88-pine">پیشرفت دوره</strong><div class="u-text-sm-soft">۷۸٪ تکمیل‌شده</div></div>
        </div>
      </div>
      <div class="stack-card float-2">
        <div class="progress-ring">
          <div class="ring"></div>
          <div><strong class="u-text-88">گواهی‌نامه</strong><div class="u-text-sm-opacity">آماده دریافت</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section u-pt-0">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">مسیرهای یادگیری</p><h2>دسته‌بندی دوره‌ها</h2></div>
    </div>
    <div class="cat-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="courses.php?cat=<?= (int)$cat['id'] ?>" class="cat-card reveal">
        <span class="ic"><?= $cat['icon'] ?></span>
        <div class="name"><?= h($cat['name']) ?></div>
        <div class="cnt"><?= (int)$cat['count'] ?> دوره</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">پیشنهاد ویژه</p>
        <h2>دوره‌های محبوب</h2>
        <p>دوره‌هایی که بیشترین استقبال دانشجویان را داشته‌اند.</p>
      </div>
      <a href="courses.php" class="btn btn-ghost">همه دوره‌ها ←</a>
    </div>
    <div class="slider-wrap">
      <button type="button" class="slider-arrow prev" data-slider-prev="featuredSlider" aria-label="قبلی">‹</button>
      <button type="button" class="slider-arrow next" data-slider-next="featuredSlider" aria-label="بعدی">›</button>
      <div class="course-grid slider-track" id="featuredSlider">
        <?php foreach ($featured as $c): ?>
        <div class="ledger-card reveal">
          <div class="punch-holes"><span></span><span></span><span></span></div>
          <div class="course-card-img">
            <img src="<?= h($c['image']) ?>" alt="<?= h($c['title']) ?>">
            <span class="badge-level"><?= h($c['level']) ?></span>
          </div>
          <div class="course-card-body">
            <div class="course-cat"><?= h($c['category_name']) ?></div>
            <a href="course-detail.php?id=<?= (int)$c['id'] ?>" class="course-title-link"><?= h($c['title']) ?></a>
            <div class="course-meta"><span class="star">★ <?= h($c['rating']) ?></span><span>· <?= number_format($c['students']) ?> دانشجو</span></div>
            <div class="course-price-row">
              <div><?php if ($c['old_price'] > 0): ?><span class="price-old"><?= fmt_price($c['old_price']) ?></span><?php endif; ?><span class="price-now"><?= fmt_price($c['price']) ?></span></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="slider-dots" data-slider-dots="featuredSlider"></div>
    </div>
  </div>
</section>

<section class="section u-bg-cream-soft">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">چرا آکادمی دانش؟</p><h2>یادگیری که به نتیجه می‌رسد</h2></div>
    </div>
    <div class="why-grid">
      <div class="why-item reveal"><div class="ic">📓</div><div><h3>یادگیری پروژه‌محور</h3><p>هر دوره با ساخت پروژه‌های واقعی همراه است، نه فقط تماشای ویدیو.</p></div></div>
      <div class="why-item reveal delay-1"><div class="ic">🧑‍🏫</div><div><h3>مدرسان متخصص</h3><p>مدرسانی که خودشان سال‌ها در همان حوزه فعالیت حرفه‌ای داشته‌اند.</p></div></div>
      <div class="why-item reveal delay-2"><div class="ic">🎓</div><div><h3>گواهی پایان دوره</h3><p>پس از اتمام هر دوره، گواهی معتبر برای رزومه شما صادر می‌شود.</p></div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">تجربه دانشجویان</p><h2>چه کسانی با آکادمی دانش رشد کرده‌اند؟</h2></div>
    </div>
    <div class="slider-wrap">
      <button type="button" class="slider-arrow prev" data-slider-prev="testiSlider" aria-label="قبلی">‹</button>
      <button type="button" class="slider-arrow next" data-slider-next="testiSlider" aria-label="بعدی">›</button>
      <div class="testi-row slider-track" id="testiSlider">
        <div class="testi-card reveal">
          <div class="quote-mark">”</div>
          <p>دوره برنامه‌نویسی وب باعث شد بعد از شش ماه بتوانم به‌عنوان فرانت‌اند مشغول به کار شوم.</p>
          <div class="testi-person"><div class="avatar">ر</div><div><b>رضا اکبرپور</b><span>دانشجوی دوره برنامه‌نویسی وب</span></div></div>
        </div>
        <div class="testi-card reveal delay-1">
          <div class="quote-mark">”</div>
          <p>کیفیت تدریس فوق‌العاده بود. مدرس هر مفهوم را با مثال‌های واقعی توضیح می‌داد.</p>
          <div class="testi-person"><div class="avatar">ف</div><div><b>فاطمه نوری</b><span>دانشجوی دوره UI/UX</span></div></div>
        </div>
        <div class="testi-card reveal delay-2">
          <div class="quote-mark">”</div>
          <p>پشتیبانی سریع و محتوای به‌روز از نقاط قوت آکادمی دانش است.</p>
          <div class="testi-person"><div class="avatar">م</div><div><b>محمد سلطانی</b><span>دانشجوی دوره علوم داده</span></div></div>
        </div>
      </div>
      <div class="slider-dots" data-slider-dots="testiSlider"></div>
    </div>
  </div>
</section>

<section class="section u-bg-cream-soft">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">از وبلاگ آکادمی دانش</p><h2>آخرین مقالات</h2></div>
      <a href="blog.php" class="btn btn-ghost">همه مقالات ←</a>
    </div>
    <div class="slider-wrap">
      <button type="button" class="slider-arrow prev" data-slider-prev="articleSlider" aria-label="قبلی">‹</button>
      <button type="button" class="slider-arrow next" data-slider-next="articleSlider" aria-label="بعدی">›</button>
      <div class="article-row slider-track" id="articleSlider">
        <?php foreach ($articles as $a): ?>
        <div class="ledger-card reveal">
          <div class="punch-holes"><span></span><span></span><span></span></div>
          <div class="article-card-img"><img src="<?= h($a['image']) ?>" alt="<?= h($a['title']) ?>"></div>
          <div class="article-card-body">
            <span class="tag"><?= h($a['category']) ?></span>
            <h3><a href="article-detail.php?id=<?= (int)$a['id'] ?>" class="u-color-pine"><?= h($a['title']) ?></a></h3>
            <p><?= h($a['summary']) ?></p>
            <div class="article-card-meta"><span><?= h($a['author']) ?></span><span><?= fmt_date($a['created_at']) ?></span></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="slider-dots" data-slider-dots="articleSlider"></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <h2>آماده شروع یادگیری هستید؟</h2>
        <p>همین امروز ثبت‌نام کنید و به جمع هزاران دانشجوی آکادمی دانش بپیوندید.</p>
      </div>
      <a href="signup.php" class="btn btn-primary">ثبت‌نام رایگان</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
