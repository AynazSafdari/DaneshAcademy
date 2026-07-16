<?php
$base_url = '';
$asset_prefix = '';
$extra_css = ['article-detail'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

$page_title = $article ? $article['title'] : 'مقاله یافت نشد';

$related = [];
if ($article) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? AND category = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$article['id'], $article['category']]);
    $related = $stmt->fetchAll();

    if (count($related) < 3) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$article['id']]);
        $related = $stmt->fetchAll();
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!$article): ?>
<section class="section u-text-center">
  <div class="container">
    <div class="u-text-2_6">🔍</div>
    <h2>مقاله مورد نظر یافت نشد</h2>
    <a href="blog.php" class="btn btn-primary u-mt-1">بازگشت به مقالات</a>
  </div>
</section>
<?php else: ?>

<section class="section u-pt-2_5">
  <div class="container">
    <div class="article-wrap">
      <div class="breadcrumb"><a href="index.php">خانه</a> / <a href="blog.php">مقالات</a> / <span><?= h($article['title']) ?></span></div>
      <span class="tag"><?= h($article['category']) ?></span>
      <h1 class="u-mt-0_8"><?= h($article['title']) ?></h1>
      <div class="article-meta-row">
        <div class="avatar"><?= h(mb_substr($article['author'], -1)) ?></div>
        <div>
          <strong class="u-color-pine-block"><?= h($article['author']) ?></strong>
          <span><?= fmt_date($article['created_at']) ?></span>
        </div>
      </div>
      <img class="article-hero-img" src="<?= h($article['image']) ?>" alt="<?= h($article['title']) ?>">
      <div class="article-body">
        <?php foreach (array_filter(explode("\n\n", $article['content'])) as $para): ?>
        <p><?= h(trim($para)) ?></p>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="article-wrap">
      <h3 class="u-mt-xl">مقالات مرتبط</h3>
      <div class="related-row">
        <?php foreach ($related as $r): ?>
        <div class="ledger-card">
          <div class="punch-holes"><span></span><span></span><span></span></div>
          <div class="article-card-img"><img src="<?= h($r['image']) ?>" alt="<?= h($r['title']) ?>"></div>
          <div class="article-card-body">
            <h3><a href="article-detail.php?id=<?= (int)$r['id'] ?>" class="u-color-pine"><?= h($r['title']) ?></a></h3>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
