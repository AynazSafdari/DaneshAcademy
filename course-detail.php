<?php
$base_url = '';
$asset_prefix = '';
$extra_css = ['course-detail'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name
    FROM courses c
    JOIN categories cat ON cat.id = c.category_id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$course = $stmt->fetch();

$page_title = $course ? $course['title'] : 'دوره یافت نشد';

// sabt nam dar dore
$enroll_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll']) && $course) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
    $userId = $_SESSION['user']['id'];
    $check = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$userId, $course['id']]);
    if ($check->fetch()) {
        $enroll_message = 'شما قبلاً در این دوره ثبت‌نام کرده‌اید.';
    } else {
        $ins = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $ins->execute([$userId, $course['id']]);
        $pdo->prepare("UPDATE courses SET students = students + 1 WHERE id = ?")->execute([$course['id']]);
        $enroll_message = 'ثبت‌نام شما با موفقیت انجام شد! 🎉';
    }
}

$syllabusItems = $course ? array_filter(array_map('trim', explode("\n", $course['syllabus']))) : [];

require __DIR__ . '/includes/header.php';
?>

<?php if (!$course): ?>
<section class="section u-text-center">
  <div class="container">
    <div class="u-text-2_6">🔍</div>
    <h2>دوره مورد نظر یافت نشد</h2>
    <p>ممکن است این دوره حذف شده یا آدرس آن اشتباه باشد.</p>
    <a href="courses.php" class="btn btn-primary u-mt-1">بازگشت به دوره‌ها</a>
  </div>
</section>
<?php else: ?>

<section class="section u-pt-2_5">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / <a href="courses.php">دوره‌ها</a> / <span><?= h($course['title']) ?></span></div>
    <div class="detail-layout">
      <div>
        <img class="detail-hero-img" src="<?= h($course['image']) ?>" alt="<?= h($course['title']) ?>">
        <span class="tag"><?= h($course['category_name']) ?></span>
        <h1 class="u-mt-0_8"><?= h($course['title']) ?></h1>
        <div class="detail-meta-row">
          <span class="course-meta"><span class="star">★ <?= h($course['rating']) ?></span></span>
          <span class="course-meta">· <?= number_format($course['students']) ?> دانشجو</span>
          <span class="badge-level"><?= h($course['level']) ?></span>
        </div>
        <div class="instructor-pill">
          <div class="avatar"><?= h(mb_substr($course['instructor'], -1)) ?></div>
          <div><strong class="u-color-pine"><?= h($course['instructor']) ?></strong></div>
        </div>

        <div class="detail-tabs-content">
          <h3>درباره این دوره</h3>
          <p><?= nl2br(h($course['description'])) ?></p>
          <h3>سرفصل‌های دوره</h3>
          <ul class="syllabus-list">
            <?php foreach ($syllabusItems as $i => $item): ?>
            <li><span class="num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span><span><?= h($item) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <aside class="sticky-buy">
        <div>
          <span class="price-big"><?= fmt_price($course['price']) ?></span>
          <?php if ($course['old_price'] > 0): ?><span class="price-old-big"><?= fmt_price($course['old_price']) ?></span><?php endif; ?>
        </div>
        <form method="post">
          <button type="submit" name="enroll" value="1" class="btn btn-primary btn-block u-mt-1">ثبت‌نام در دوره</button>
        </form>
        <?php if ($enroll_message): ?>
          <p class="u-text-sm-soft u-mt-0_6"><?= h($enroll_message) ?></p>
        <?php endif; ?>
        <ul class="buy-meta-list">
          <li><span>مدت دوره</span><b><?= (int)$course['hours'] ?> ساعت</b></li>
          <li><span>تعداد درس‌ها</span><b><?= (int)$course['lessons'] ?> درس</b></li>
          <li><span>سطح</span><b><?= h($course['level']) ?></b></li>
          <li><span>دانشجویان</span><b><?= number_format($course['students']) ?></b></li>
          <li><span>گواهی پایان دوره</span><b>دارد ✓</b></li>
        </ul>
      </aside>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
