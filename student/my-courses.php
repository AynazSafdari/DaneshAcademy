<?php
$page_title = 'دوره‌های من';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_login('../login.php');

if ($_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$studentId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN categories cat ON cat.id = c.category_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$studentId]);
$myCourses = $stmt->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<div class="admin-card">
  <div class="admin-card-head"><h3>دوره‌های ثبت‌نام‌شده (<?= count($myCourses) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>دوره</th><th>مدرس</th><th>دسته‌بندی</th><th>تاریخ ثبت‌نام</th><th>دسترسی</th></tr>
      </thead>
      <tbody>
        <?php if (empty($myCourses)): ?>
        <tr class="empty-row"><td colspan="5">هنوز در هیچ دوره‌ای ثبت‌نام نکرده‌اید. <a href="../courses.php" class="u-color-pine">مشاهده دوره‌های موجود</a></td></tr>
        <?php else: foreach ($myCourses as $c): ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($c['image']) ?>" alt="">
              <span class="title"><?= h($c['title']) ?></span>
            </div>
          </td>
          <td><?= h($c['instructor']) ?></td>
          <td><?= h($c['category_name']) ?></td>
          <td><?= fmt_date($c['enrolled_at']) ?></td>
          <td>
            <a class="btn btn-primary btn-sm" href="../course-detail.php?id=<?= $c['id'] ?>">ورود به دوره</a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
