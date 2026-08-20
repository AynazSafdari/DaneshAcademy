<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_login('../login.php');

if ($_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$current_student_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' | پنل دانشجو' : 'پنل دانشجو | آکادمی دانش' ?></title>
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/admin-base.css">
<link rel="stylesheet" href="../assets/css/Desktop/admin-layout.css">
<link rel="stylesheet" href="../assets/css/Tablet/admin-layout.css">
<link rel="stylesheet" href="../assets/css/Mobile/admin-layout.css">
</head>
<body class="admin-body">

<aside class="admin-sidebar">
  <div class="admin-logo"><span class="mark">آ</span> پنل دانشجو</div>
  <nav class="admin-nav">
    <a href="dashboard.php" class="<?= $current_student_page === 'dashboard.php' ? 'active' : '' ?>"><span class="ic">📊</span> داشبورد</a>
    <a href="my-courses.php" class="<?= $current_student_page === 'my-courses.php' ? 'active' : '' ?>"><span class="ic">🎓</span> دوره‌های من</a>
    <a href="my-bookmarks.php" class="<?= $current_student_page === 'my-bookmarks.php' ? 'active' : '' ?>"><span class="ic">🔖</span> نشان‌شده‌های من</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="avatar"><?= h(mb_substr($_SESSION['user']['name'], -1)) ?></div>
      <div><b><?= h($_SESSION['user']['name']) ?></b><span>دانشجو</span></div>
    </div>
    <a href="../logout.php" class="btn btn-outline btn-sm btn-block u-social-light-2">خروج از حساب</a>
    <a href="../index.php" class="btn btn-ghost btn-sm btn-block u-text-light-mt">بازگشت به سایت ←</a>
  </div>
</aside>
<div class="admin-overlay"></div>

<main class="admin-main">
  <div class="admin-topbar">
    <div class="u-flex-center-gap">
      <button class="admin-hamburger" aria-label="منو"><span></span><span></span><span></span></button>
      <h1><?= h($page_title ?? 'پنل دانشجو') ?></h1>
    </div>
    <?php if (!empty($topbar_action)) echo $topbar_action; ?>
  </div>

  <div class="admin-content">
