<?php
$page_title = 'رتبه‌بندی اساتید';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ranking.php';
require_teacher('login.php');

$myId = (int) $_SESSION['user']['id'];
$rankings = calculate_teacher_rankings($pdo);

require __DIR__ . '/includes_header.php';
?>

<div class="admin-card u-mb-1">
  <div class="u-list-padding">
    <p class="u-text-sm-soft">
      امتیاز نهایی از ترکیب پنج معیار محاسبه می‌شود: تعداد دوره تاییدشده (۲۸٪)، تعداد مقاله تاییدشده (۲۸٪)، میانگین امتیاز دوره‌ها با تعدیل آماری بیزی (۲۴٪)، تعداد کل دانشجویان ثبت‌نامی (۱۰٪)، و میانگین امتیازی که دانشجویان مستقیماً به شما داده‌اند با همان تعدیل آماری (۱۰٪).
    </p>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-head">
    <h3>جدول رتبه‌بندی (<?= count($rankings) ?> استاد)</h3>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>رتبه</th><th>استاد</th><th>دوره</th><th>مقاله</th><th>دانشجو</th><th>امتیاز دوره‌ها</th><th>امتیاز مستقیم دانشجویان</th><th>امتیاز نهایی</th></tr>
      </thead>
      <tbody>
        <?php if (empty($rankings)): ?>
        <tr class="empty-row"><td colspan="8">هنوز هیچ استادی در سیستم ثبت نشده است.</td></tr>
        <?php else: foreach ($rankings as $t): ?>
        <tr <?= (int)$t['id'] === $myId ? 'class="highlight-row"' : '' ?>>
          <td><span class="rank-badge <?= $t['rank'] <= 3 ? 'rank-' . $t['rank'] : '' ?>">#<?= $t['rank'] ?></span></td>
          <td>
            <strong class="u-color-pine"><?= h($t['name']) ?></strong>
            <?= (int)$t['id'] === $myId ? ' <span class="tag tag-saffron">شما</span>' : '' ?>
          </td>
          <td><?= (int)$t['course_count'] ?></td>
          <td><?= (int)$t['article_count'] ?></td>
          <td><?= number_format($t['total_students']) ?></td>
          <td>★ <?= $t['bayesian_course_rating'] ?></td>
          <td>★ <?= $t['bayesian_teacher_rating'] ?><span class="u-text-sm-soft"> (<?= (int)$t['rating_count'] ?>)</span></td>
          <td><strong class="u-color-pine"><?= $t['final_score'] ?></strong> / ۱۰۰</td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
