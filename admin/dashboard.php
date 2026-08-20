<?php
$page_title = 'داشبورد مدیریت';
$extra_admin_css = ['dashboard'];
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$statCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'approved'")->fetchColumn();
$statArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'approved'")->fetchColumn();
$statUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$statTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND status = 'approved'")->fetchColumn();
$statUnreadMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$statPendingCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetchColumn();
$statPendingArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'pending'")->fetchColumn();
$statPendingTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND status = 'pending'")->fetchColumn();
$statPendingTotal = $statPendingCourses + $statPendingArticles + $statPendingTeachers;

$recentCourses = $pdo->query("SELECT title, instructor, price FROM courses WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMessages = $pdo->query("SELECT name, subject, is_read FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/ranking.php';
$topTeachers = array_slice(calculate_teacher_rankings($pdo), 0, 3);

require __DIR__ . '/includes_header.php';
?>

<?php if ($statPendingTotal > 0): ?>
<div class="admin-banner banner-pending">
  <?= $statPendingTotal ?> مورد (<?= $statPendingTeachers ?> ثبت‌نام استاد، <?= $statPendingCourses ?> دوره و <?= $statPendingArticles ?> مقاله) در انتظار تایید شماست.
  <a href="teachers.php?tab=pending-teachers" class="u-inline-link">بررسی و تایید ←</a>
</div>
<?php endif; ?>

<div class="stat-cards">
  <div class="stat-card"><div class="ic bg1">🎓</div><div><b><?= number_format($statCourses) ?></b><span>دوره‌های منتشرشده</span></div></div>
  <div class="stat-card"><div class="ic bg2">📝</div><div><b><?= number_format($statArticles) ?></b><span>مقالات منتشرشده</span></div></div>
  <div class="stat-card"><div class="ic bg3">👥</div><div><b><?= number_format($statUsers) ?></b><span>دانشجویان ثبت‌نام‌شده</span></div></div>
  <div class="stat-card"><div class="ic bg4">🧑</div><div><b><?= number_format($statTeachers) ?></b><span>اساتید</span></div></div>
</div>

<div class="recent-grid">
  <div class="admin-card">
    <div class="admin-card-head"><h3>آخرین دوره‌های اضافه‌شده</h3><a href="courses.php" class="btn btn-ghost btn-sm">مدیریت ←</a></div>
    <div class="u-list-padding">
      <ul class="mini-list">
        <?php if (empty($recentCourses)): ?>
        <li>دوره‌ای ثبت نشده است.</li>
        <?php else: foreach ($recentCourses as $c): ?>
        <li><div><div class="name"><?= h($c['title']) ?></div><div class="sub"><?= h($c['instructor']) ?></div></div><span><?= fmt_price($c['price']) ?></span></li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-head"><h3>آخرین پیام‌ها</h3><a href="messages.php" class="btn btn-ghost btn-sm">مشاهده ←</a></div>
    <div class="u-list-padding">
      <ul class="mini-list">
        <?php if (empty($recentMessages)): ?>
        <li>پیامی دریافت نشده است.</li>
        <?php else: foreach ($recentMessages as $m): ?>
        <li><div><div class="name"><?= h($m['name']) ?></div><div class="sub"><?= h($m['subject']) ?></div></div><span class="status-pill <?= $m['is_read'] ? 'read' : 'unread' ?>"><?= $m['is_read'] ? 'خوانده‌شده' : 'جدید' ?></span></li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-head"><h3>اساتید برتر</h3><a href="teachers.php?tab=ranking" class="btn btn-ghost btn-sm">جدول کامل ←</a></div>
    <div class="u-list-padding">
      <ul class="mini-list">
        <?php if (empty($topTeachers)): ?>
        <li>هنوز استادی ثبت نشده است.</li>
        <?php else: foreach ($topTeachers as $t): ?>
        <li>
          <div class="u-flex-center-gap">
            <span class="rank-badge <?= $t['rank'] <= 3 ? 'rank-' . $t['rank'] : '' ?>">#<?= $t['rank'] ?></span>
            <div><div class="name"><?= h($t['name']) ?></div><div class="sub"><?= (int)$t['course_count'] ?> دوره · <?= (int)$t['article_count'] ?> مقاله</div></div>
          </div>
          <span><strong class="u-color-pine"><?= $t['final_score'] ?></strong></span>
        </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
