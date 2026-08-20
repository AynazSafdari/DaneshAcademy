<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_teacher('login.php');

$current_teacher_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' | پنل استاد آکادمی دانش' : 'پنل استاد | آکادمی دانش' ?></title>
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
  <div class="admin-logo"><span class="mark">آ</span> پنل استاد</div>
  <nav class="admin-nav">
    <a href="dashboard.php" class="<?= $current_teacher_page === 'dashboard.php' ? 'active' : '' ?>"><span class="ic">📊</span> داشبورد</a>
    <a href="courses.php" class="<?= $current_teacher_page === 'courses.php' ? 'active' : '' ?>"><span class="ic">🎓</span> دوره‌های من</a>
    <a href="articles.php" class="<?= $current_teacher_page === 'articles.php' ? 'active' : '' ?>"><span class="ic">📝</span> مقالات من</a>
    <a href="ranking.php" class="<?= $current_teacher_page === 'ranking.php' ? 'active' : '' ?>"><span class="ic">🏆</span> رتبه‌بندی اساتید</a>
    <a href="profile-settings.php" class="<?= $current_teacher_page === 'profile-settings.php' ? 'active' : '' ?>"><span class="ic">⚙️</span> تنظیمات پروفایل</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="avatar"><?= h(mb_substr($_SESSION['user']['name'], -1)) ?></div>
      <div><b><?= h($_SESSION['user']['name']) ?></b><span>استاد</span></div>
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
      <h1><?= h($page_title ?? 'پنل استاد') ?></h1>
    </div>
    <?php if (!empty($topbar_action)) echo $topbar_action; ?>
  </div>

  <div class="admin-content">
