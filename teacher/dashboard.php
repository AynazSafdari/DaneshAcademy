<?php
$page_title = 'داشبورد استاد';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ranking.php';
require_teacher('login.php');

$teacherId = (int) $_SESSION['user']['id'];

$statApproved = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'approved'");
$statApproved->execute([$teacherId]);
$statApprovedCourses = $statApproved->fetchColumn();

$statPending = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'pending'");
$statPending->execute([$teacherId]);
$statPendingCourses = $statPending->fetchColumn();

$statArticlesApproved = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE teacher_id = ? AND status = 'approved'");
$statArticlesApproved->execute([$teacherId]);
$statApprovedArticles = $statArticlesApproved->fetchColumn();

$statStudents = $pdo->prepare("SELECT COALESCE(SUM(students), 0) FROM courses WHERE teacher_id = ? AND status = 'approved'");
$statStudents->execute([$teacherId]);
$statTotalStudents = $statStudents->fetchColumn();

$myRank = get_teacher_rank($pdo, $teacherId);

$recentCourses = $pdo->prepare("SELECT title, status, students FROM courses WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
$recentCourses->execute([$teacherId]);
$recentCourses = $recentCourses->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><div class="ic bg1">🎓</div><div><b><?= number_format($statApprovedCourses) ?></b><span>دوره تاییدشده</span></div></div>
  <div class="stat-card"><div class="ic bg4">⏳</div><div><b><?= number_format($statPendingCourses) ?></b><span>در انتظار تایید</span></div></div>
  <div class="stat-card"><div class="ic bg2">📝</div><div><b><?= number_format($statApprovedArticles) ?></b><span>مقاله تاییدشده</span></div></div>
  <div class="stat-card"><div class="ic bg3">👥</div><div><b><?= number_format($statTotalStudents) ?></b><span>دانشجویان شما</span></div></div>
</div>

<div class="admin-card u-mb-1">
  <div class="admin-card-head"><h3>رتبه فعلی شما در آکادمی دانش</h3><a href="ranking.php" class="btn btn-ghost btn-sm">مشاهده جدول کامل ←</a></div>
  <div class="u-list-padding">
    <?php if ($myRank): ?>
    <div class="rank-summary-row">
      <span class="rank-badge <?= $myRank['rank'] <= 3 ? 'rank-' . $myRank['rank'] : '' ?>">#<?= $myRank['rank'] ?></span>
      <div>
        <b class="u-color-pine"><?= $myRank['final_score'] ?> امتیاز از ۱۰۰</b>
        <p class="u-text-sm-soft">
          <?= (int)$myRank['course_count'] ?> دوره · <?= (int)$myRank['article_count'] ?> مقاله · <?= number_format($myRank['total_students']) ?> دانشجو<br>
          امتیاز دوره‌ها: ★ <?= $myRank['bayesian_course_rating'] ?> — امتیاز مستقیم دانشجویان: ★ <?= $myRank['bayesian_teacher_rating'] ?> (<?= (int)$myRank['rating_count'] ?> نظر)
        </p>
      </div>
    </div>
    <?php else: ?>
    <p class="u-text-sm-soft">هنوز دوره یا مقاله تاییدشده‌ای ندارید تا رتبه محاسبه شود.</p>
    <?php endif; ?>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3>آخرین دوره‌های شما</h3><a href="courses.php" class="btn btn-ghost btn-sm">مدیریت ←</a></div>
  <div class="u-list-padding">
    <ul class="mini-list">
      <?php if (empty($recentCourses)): ?>
      <li>هنوز دوره‌ای ثبت نکرده‌اید.</li>
      <?php else: foreach ($recentCourses as $c):
        $statusLabel = ['pending' => 'در انتظار تایید', 'approved' => 'تاییدشده', 'rejected' => 'رد‌شده'][$c['status']];
      ?>
      <li>
        <div><div class="name"><?= h($c['title']) ?></div><div class="sub"><?= number_format($c['students']) ?> دانشجو</div></div>
        <span class="status-badge status-<?= $c['status'] ?>"><?= $statusLabel ?></span>
      </li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
