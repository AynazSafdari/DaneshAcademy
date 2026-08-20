<?php
$page_title = 'نشان‌شده‌های من';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_login('../login.php');

if ($_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$studentId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name, b.created_at AS bookmarked_at, b.id AS bookmark_id
    FROM bookmarks b
    JOIN courses c ON c.id = b.content_id AND b.content_type = 'course'
    JOIN categories cat ON cat.id = c.category_id
    WHERE b.user_id = ? AND c.status = 'approved'
    ORDER BY b.created_at DESC
");
$stmt->execute([$studentId]);
$bookmarkedCourses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT a.*, b.created_at AS bookmarked_at, b.id AS bookmark_id
    FROM bookmarks b
    JOIN articles a ON a.id = b.content_id AND b.content_type = 'article'
    WHERE b.user_id = ? AND a.status = 'approved'
    ORDER BY b.created_at DESC
");
$stmt->execute([$studentId]);
$bookmarkedArticles = $stmt->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<div class="admin-card u-mb-1">
  <div class="admin-card-head"><h3>دوره‌های نشان‌شده (<?= count($bookmarkedCourses) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>دوره</th><th>مدرس</th><th>دسته‌بندی</th><th>تاریخ نشان‌کردن</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($bookmarkedCourses)): ?>
        <tr class="empty-row"><td colspan="5">هنوز دوره‌ای را نشان نکرده‌اید. <a href="../courses.php" class="u-color-pine">مشاهده دوره‌ها</a></td></tr>
        <?php else: foreach ($bookmarkedCourses as $c): ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($c['image']) ?>" alt="">
              <span class="title"><?= h($c['title']) ?></span>
            </div>
          </td>
          <td><?= h($c['instructor']) ?></td>
          <td><?= h($c['category_name']) ?></td>
          <td><?= fmt_date($c['bookmarked_at']) ?></td>
          <td>
            <div class="row-actions">
              <a class="btn btn-primary btn-sm" href="../course-detail.php?id=<?= $c['id'] ?>">مشاهده دوره</a>
              <a class="icon-btn danger" title="حذف از نشان‌ها" href="#" onclick="removeBookmark(this, 'course', <?= $c['id'] ?>); return false;">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3>مقالات نشان‌شده (<?= count($bookmarkedArticles) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>مقاله</th><th>دسته‌بندی</th><th>تاریخ نشان‌کردن</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($bookmarkedArticles)): ?>
        <tr class="empty-row"><td colspan="4">هنوز مقاله‌ای را نشان نکرده‌اید. <a href="../blog.php" class="u-color-pine">مشاهده مقالات</a></td></tr>
        <?php else: foreach ($bookmarkedArticles as $a): ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($a['image']) ?>" alt="">
              <span class="title"><?= h($a['title']) ?></span>
            </div>
          </td>
          <td><span class="tag"><?= h($a['category']) ?></span></td>
          <td><?= fmt_date($a['bookmarked_at']) ?></td>
          <td>
            <div class="row-actions">
              <a class="btn btn-primary btn-sm" href="../article-detail.php?id=<?= $a['id'] ?>">مشاهده مقاله</a>
              <a class="icon-btn danger" title="حذف از نشان‌ها" href="#" onclick="removeBookmark(this, 'article', <?= $a['id'] ?>); return false;">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  async function removeBookmark(el, type, id) {
    const row = el.closest('tr');
    try {
      const res = await fetch('../bookmark-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `type=${type}&id=${id}`
      });
      const data = await res.json();
      if (data.ok && !data.bookmarked) {
        row.remove();
      }
    } catch (err) {
      alert('خطا در حذف نشان.');
    }
  }
</script>

<?php require __DIR__ . '/includes_footer.php'; ?>
