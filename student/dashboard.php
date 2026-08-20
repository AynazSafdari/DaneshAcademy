<?php
$page_title = 'داشبورد دانشجو';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_login('../login.php');

if ($_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$studentId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
$stmt->execute([$studentId]);
$totalEnrollments = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT c.title, c.image, c.instructor, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
$stmt->execute([$studentId]);
$recentEnrollments = $stmt->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><div class="ic bg1">🎓</div><div><b><?= number_format($totalEnrollments) ?></b><span>دوره ثبت‌نام‌شده</span></div></div>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3>آخرین دوره‌های ثبت‌نامی</h3><a href="my-courses.php" class="btn btn-ghost btn-sm">مشاهده همه ←</a></div>
  <div class="u-list-padding">
    <ul class="mini-list">
      <?php if (empty($recentEnrollments)): ?>
      <li>هنوز در هیچ دوره‌ای ثبت‌نام نکرده‌اید. <a href="../courses.php" class="u-color-pine">مشاهده دوره‌ها</a></li>
      <?php else: foreach ($recentEnrollments as $e): ?>
      <li><div><div class="name"><?= h($e['title']) ?></div><div class="sub"><?= h($e['instructor']) ?></div></div><span class="u-text-sm-soft"><?= fmt_date($e['enrolled_at']) ?></span></li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
