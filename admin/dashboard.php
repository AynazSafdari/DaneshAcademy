<?php
$page_title = 'داشبورد مدیریت';
$extra_admin_css = ['dashboard'];
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$statCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$statArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$statUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$statUnreadMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

$recentCourses = $pdo->query("SELECT title, instructor, price FROM courses ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMessages = $pdo->query("SELECT name, subject, is_read FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><div class="ic bg1">🎓</div><div><b><?= number_format($statCourses) ?></b><span>تعداد دوره‌ها</span></div></div>
  <div class="stat-card"><div class="ic bg2">📝</div><div><b><?= number_format($statArticles) ?></b><span>تعداد مقالات</span></div></div>
  <div class="stat-card"><div class="ic bg3">👥</div><div><b><?= number_format($statUsers) ?></b><span>کاربران ثبت‌نام‌شده</span></div></div>
  <div class="stat-card"><div class="ic bg4">✉️</div><div><b><?= number_format($statUnreadMessages) ?></b><span>پیام‌های خوانده‌نشده</span></div></div>
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
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
