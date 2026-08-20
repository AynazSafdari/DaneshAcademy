<?php
$page_title = 'مقالات';
$base_url = '';
$asset_prefix = '';
$extra_css = ['blog'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$activeCat = $_GET['cat'] ?? 'همه';

$categories = $pdo->query("SELECT DISTINCT category FROM articles WHERE status = 'approved' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

if ($activeCat !== 'همه') {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE category = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt->execute([$activeCat]);
} else {
    $stmt = $pdo->query("SELECT * FROM articles WHERE status = 'approved' ORDER BY created_at DESC");
}
$articles = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / مقالات</div>
    <h1>وبلاگ آکادمی دانش</h1>
    <p>یادداشت‌ها و راهنماهایی برای یادگیری بهتر و رشد حرفه‌ای.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="blog-toolbar">
      <a href="blog.php" class="blog-cat-pill <?= $activeCat === 'همه' ? 'active' : '' ?>">همه</a>
      <?php foreach ($categories as $cat): ?>
      <a href="blog.php?cat=<?= urlencode($cat) ?>" class="blog-cat-pill <?= $activeCat === $cat ? 'active' : '' ?>"><?= h($cat) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="blog-grid">
      <?php foreach ($articles as $a): ?>
      <div class="ledger-card">
        <div class="punch-holes"><span></span><span></span><span></span></div>
        <div class="article-card-img"><img src="<?= h($a['image']) ?>" alt="<?= h($a['title']) ?>"></div>
        <div class="article-card-body">
          <span class="tag"><?= h($a['category']) ?></span>
          <h3><a href="article-detail.php?id=<?= (int)$a['id'] ?>" class="u-color-pine"><?= h($a['title']) ?></a></h3>
          <p><?= h($a['summary']) ?></p>
          <div class="article-card-meta"><span><?= h($a['author']) ?></span><span><?= fmt_date($a['created_at']) ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
