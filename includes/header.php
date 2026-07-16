<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' | آکادمی دانش' : 'آکادمی دانش | یادگیری آنلاین' ?></title>
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/main.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Desktop/layout.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Tablet/layout.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Mobile/layout.css">
<?php if (!empty($extra_css)): foreach ($extra_css as $css): ?>
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Desktop/<?= $css ?>.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Tablet/<?= $css ?>.css">
<link rel="stylesheet" href="<?= $asset_prefix ?? '' ?>assets/css/Mobile/<?= $css ?>.css">
<?php endforeach; endif; ?>
</head>
<body<?= isset($body_class) ? ' class="' . h($body_class) . '"' : '' ?>>

<header class="site-header">
  <div class="container nav-wrap">
    <a href="<?= $base_url ?? '' ?>index.php" class="logo"><span class="mark">آ</span> آکادمی دانش</a>
    <nav class="nav-links">
      <a href="<?= $base_url ?? '' ?>index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">خانه</a>
      <a href="<?= $base_url ?? '' ?>courses.php" class="<?= $current_page === 'courses.php' ? 'active' : '' ?>">دوره‌ها</a>
      <a href="<?= $base_url ?? '' ?>blog.php" class="<?= $current_page === 'blog.php' ? 'active' : '' ?>">مقالات</a>
      <a href="<?= $base_url ?? '' ?>about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">درباره ما</a>
      <a href="<?= $base_url ?? '' ?>contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">تماس با ما</a>
    </nav>
    <div class="nav-actions">
      <div data-user-slot>
        <?php if (is_logged_in()): ?>
          <span class="u-text-88-pine u-fw-700">سلام، <?= h($_SESSION['user']['name']) ?></span>
          <a href="<?= $base_url ?? '' ?>logout.php" class="btn btn-ghost btn-sm">خروج</a>
        <?php else: ?>
          <a href="<?= $base_url ?? '' ?>login.php" class="btn btn-outline btn-sm">ورود / ثبت‌نام</a>
        <?php endif; ?>
      </div>
      <button class="hamburger" aria-label="منو"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
<div class="nav-overlay"></div>
