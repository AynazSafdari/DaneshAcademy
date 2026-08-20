<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' | آکادمی دانش ادمین' : 'پنل مدیریت | آکادمی دانش' ?></title>
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/admin-base.css">
<link rel="stylesheet" href="../assets/css/Desktop/admin-layout.css">
<link rel="stylesheet" href="../assets/css/Tablet/admin-layout.css">
<link rel="stylesheet" href="../assets/css/Mobile/admin-layout.css">
<?php if (!empty($extra_admin_css)): foreach ($extra_admin_css as $css): ?>
<link rel="stylesheet" href="../assets/css/Desktop/<?= $css ?>.css">
<link rel="stylesheet" href="../assets/css/Tablet/<?= $css ?>.css">
<link rel="stylesheet" href="../assets/css/Mobile/<?= $css ?>.css">
<?php endforeach; endif; ?>
</head>
<body class="admin-body">

<aside class="admin-sidebar">
  <div class="admin-logo"><span class="mark">آ</span> آکادمی دانش ادمین</div>
  <nav class="admin-nav">
    <a href="dashboard.php" class="<?= $current_admin_page === 'dashboard.php' ? 'active' : '' ?>"><span class="ic">📊</span> داشبورد</a>
    <a href="courses.php" class="<?= $current_admin_page === 'courses.php' ? 'active' : '' ?>"><span class="ic">🎓</span> مدیریت دوره‌ها</a>
    <a href="categories.php" class="<?= $current_admin_page === 'categories.php' ? 'active' : '' ?>"><span class="ic">🗂️</span> دسته‌بندی‌ها</a>
    <a href="articles.php" class="<?= $current_admin_page === 'articles.php' ? 'active' : '' ?>"><span class="ic">📝</span> مدیریت مقالات</a>
    <a href="teachers.php" class="<?= $current_admin_page === 'teachers.php' ? 'active' : '' ?>">
      <span class="ic">🧑</span> اساتید و تایید محتوا
      <?php
        $pendingCountForBadge = (int) $pdo->query("SELECT
          (SELECT COUNT(*) FROM courses WHERE status='pending') +
          (SELECT COUNT(*) FROM articles WHERE status='pending') +
          (SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending')
        ")->fetchColumn();
      ?>
      <?php if ($pendingCountForBadge > 0): ?>
        <span class="nav-pending-badge"><?= $pendingCountForBadge ?></span>
      <?php endif; ?>
    </a>
    <a href="users.php" class="<?= $current_admin_page === 'users.php' ? 'active' : '' ?>"><span class="ic">👥</span> دانشجویان</a>
    <a href="messages.php" class="<?= $current_admin_page === 'messages.php' ? 'active' : '' ?>"><span class="ic">✉️</span> پیام‌های تماس</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="avatar"><?= h(mb_substr($_SESSION['user']['name'], -1)) ?></div>
      <div><b><?= h($_SESSION['user']['name']) ?></b><span>مدیر سامانه</span></div>
    </div>
    <a href="../logout.php" class="btn btn-outline btn-sm btn-block u-social-light-2">خروج از پنل</a>
    <a href="../index.php" class="btn btn-ghost btn-sm btn-block u-text-light-mt">بازگشت به سایت ←</a>
  </div>
</aside>
<div class="admin-overlay"></div>

<main class="admin-main">
  <div class="admin-topbar">
    <div class="u-flex-center-gap">
      <button class="admin-hamburger" aria-label="منو"><span></span><span></span><span></span></button>
      <h1><?= h($page_title ?? 'پنل مدیریت') ?></h1>
    </div>
    <?php if (!empty($topbar_action)) echo $topbar_action; ?>
  </div>

  <div class="admin-content">
