<?php
$page_title = 'اساتید و رتبه‌بندی';
$base_url = '';
$asset_prefix = '';
$extra_css = ['teachers'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ranking.php';

$rankings = calculate_teacher_rankings($pdo);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / اساتید</div>
    <h1>اساتید آکادمی دانش</h1>
    <p>پیش از انتخاب دوره، عملکرد اساتید را بر اساس تعداد دوره‌ها، مقالات منتشرشده، تعداد دانشجویان و امتیاز مقایسه کنید. برای مشاهده کامل دوره‌ها و مقالات هر استاد، روی کارتش کلیک کنید.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (empty($rankings)): ?>
    <p class="u-text-sm-soft">هنوز استادی در سیستم ثبت نشده است.</p>
    <?php else: ?>

    <div class="teacher-public-grid">
      <?php foreach ($rankings as $t): ?>
      <a href="teacher-profile.php?id=<?= (int)$t['id'] ?>" class="ledger-card reveal teacher-public-card">
        <div class="punch-holes"><span></span><span></span><span></span></div>
        <div class="teacher-public-body">
          <div class="teacher-public-top">
            <span class="rank-badge <?= $t['rank'] <= 3 ? 'rank-' . $t['rank'] : '' ?>">#<?= $t['rank'] ?></span>
            <div class="teacher-public-avatar"><?= h(mb_substr($t['name'], -1)) ?></div>
          </div>
          <h3 class="u-color-pine"><?= h($t['name']) ?></h3>
          <?php if (!empty($t['bio'])): ?>
          <p class="u-text-sm-soft"><?= h($t['bio']) ?></p>
          <?php else: ?>
          <p class="u-text-sm-soft">این استاد هنوز بیوگرافی ثبت نکرده است.</p>
          <?php endif; ?>

          <div class="teacher-public-stats">
            <div><b><?= (int)$t['course_count'] ?></b><span>دوره فعال</span></div>
            <div><b><?= (int)$t['article_count'] ?></b><span>مقاله</span></div>
            <div><b><?= number_format($t['total_students']) ?></b><span>دانشجو</span></div>
          </div>

          <div class="teacher-public-score">
            <div>
              <span class="star">★ <?= $t['bayesian_teacher_rating'] ?></span>
              <span class="u-text-sm-soft">امتیاز دانشجویان (<?= (int)$t['rating_count'] ?> نظر)</span>
            </div>
            <div class="teacher-public-final">
              <b class="u-color-pine"><?= $t['final_score'] ?></b>
              <span class="u-text-sm-soft">از ۱۰۰</span>
            </div>
          </div>

          <span class="teacher-public-view-link">مشاهده پروفایل و دوره‌ها ←</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    

    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
