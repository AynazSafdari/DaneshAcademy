<?php
$page_title = 'دوره‌ها';
$base_url = '';
$asset_prefix = '';
$extra_css = ['courses'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
foreach ($categories as &$cat) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE category_id = ? AND status = 'approved'");
    $stmt->execute([$cat['id']]);
    $cat['count'] = $stmt->fetchColumn();
}


$selectedCats = isset($_GET['cat']) ? array_map('intval', (array)$_GET['cat']) : [];
$selectedLevels = isset($_GET['level']) ? (array)$_GET['level'] : [];
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'popular';

$where = ["c.status = 'approved'"];
$params = [];

if (!empty($selectedCats)) {
    $placeholders = implode(',', array_fill(0, count($selectedCats), '?'));
    $where[] = "c.category_id IN ($placeholders)";
    $params = array_merge($params, $selectedCats);
}
if (!empty($selectedLevels)) {
    $placeholders = implode(',', array_fill(0, count($selectedLevels), '?'));
    $where[] = "c.level IN ($placeholders)";
    $params = array_merge($params, $selectedLevels);
}
if ($search !== '') {
    $where[] = "(c.title LIKE ? OR c.instructor LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$orderSql = match ($sort) {
    'newest' => 'c.created_at DESC',
    'price-low' => 'c.price ASC',
    'price-high' => 'c.price DESC',
    'rating' => 'c.rating DESC',
    'teacher-rank' => 'c.students DESC', 
    default => 'c.students DESC',
};

$sql = "
    SELECT c.*, cat.name AS category_name
    FROM courses c
    JOIN categories cat ON cat.id = c.category_id
    $whereSql
    ORDER BY $orderSql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// --- مرتب‌سازی بر اساس رتبه استاد  ---
if ($sort === 'teacher-rank') {
    require_once __DIR__ . '/includes/ranking.php';
    $teacherRankings = calculate_teacher_rankings($pdo);
    $teacherScoreMap = [];
    foreach ($teacherRankings as $t) {
        $teacherScoreMap[(int) $t['id']] = $t['final_score'];
    }

    usort($courses, function ($a, $b) use ($teacherScoreMap) {
        $scoreA = isset($a['teacher_id']) && $a['teacher_id'] ? ($teacherScoreMap[(int) $a['teacher_id']] ?? -1) : -1;
        $scoreB = isset($b['teacher_id']) && $b['teacher_id'] ? ($teacherScoreMap[(int) $b['teacher_id']] ?? -1) : -1;
        return $scoreB <=> $scoreA;
    });
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / دوره‌ها</div>
    <h1>دوره‌های آموزشی</h1>
    <p>دوره مناسب خودتان را از میان مجموعه‌ای متنوع و به‌روز پیدا کنید.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <button class="btn btn-outline btn-sm mobile-filter-toggle u-mb-1" id="mobileFilterBtn">فیلترها 🔧</button>
    <form method="get" class="courses-layout" id="filterForm">
      <aside class="filter-card" id="filterCard">
        <h4>دسته‌بندی</h4>
        <div class="filter-group">
          <?php foreach ($categories as $cat): ?>
          <label class="filter-opt">
            <input type="checkbox" name="cat[]" value="<?= $cat['id'] ?>" onchange="this.form.submit()"
              <?= in_array($cat['id'], $selectedCats) ? 'checked' : '' ?>>
            <?= $cat['icon'] ?> <?= h($cat['name']) ?> <span class="cnt"><?= $cat['count'] ?></span>
          </label>
          <?php endforeach; ?>
        </div>
        <h4>سطح دوره</h4>
        <div class="filter-group">
          <?php foreach (['مقدماتی','متوسط','پیشرفته'] as $lvl): ?>
          <label class="filter-opt">
            <input type="checkbox" name="level[]" value="<?= $lvl ?>" onchange="this.form.submit()"
              <?= in_array($lvl, $selectedLevels) ? 'checked' : '' ?>>
            <?= $lvl ?>
          </label>
          <?php endforeach; ?>
        </div>
        <a href="courses.php" class="btn btn-ghost btn-sm u-pt-0_4-0">حذف فیلترها</a>
      </aside>

      <div>
        <div class="toolbar">
          <div class="search-box">
            <input type="text" name="q" value="<?= h($search) ?>" placeholder="جستجوی دوره...">
          </div>
          <select class="sort-select" name="sort" onchange="this.form.submit()">
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>محبوب‌ترین</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>جدیدترین</option>
            <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>ارزان‌ترین</option>
            <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>گران‌ترین</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>بالاترین امتیاز</option>
            <option value="teacher-rank" <?= $sort === 'teacher-rank' ? 'selected' : '' ?>>بر اساس رتبه استاد</option>
          </select>
        </div>
        <p class="result-count u-mb-1"><?= count($courses) ?> دوره یافت شد</p>

        <?php if (empty($courses)): ?>
        <div class="empty-state">
          <div class="ic">📭</div>
          <h3>دوره‌ای یافت نشد</h3>
          <p>فیلترها یا عبارت جستجو را تغییر دهید.</p>
        </div>
        <?php else: ?>
        <div class="course-grid-page">
          <?php foreach ($courses as $c): ?>
          <div class="ledger-card">
            <div class="punch-holes"><span></span><span></span><span></span></div>
            <div class="course-card-img">
              <img src="<?= h($c['image']) ?>" alt="<?= h($c['title']) ?>">
              <span class="badge-level"><?= h($c['level']) ?></span>
            </div>
            <div class="course-card-body">
              <div class="course-cat"><?= h($c['category_name']) ?></div>
              <a href="course-detail.php?id=<?= (int)$c['id'] ?>" class="course-title-link"><?= h($c['title']) ?></a>
              <div class="course-meta"><span class="star">★ <?= h($c['rating']) ?></span><span>· <?= number_format($c['students']) ?> دانشجو</span><span>· <?= (int)$c['hours'] ?> ساعت</span></div>
              <?php if ($sort === 'teacher-rank' && !empty($c['teacher_id']) && isset($teacherScoreMap[(int)$c['teacher_id']])): ?>
              <div class="course-teacher-rank-hint">رتبه استاد: <b><?= $teacherScoreMap[(int)$c['teacher_id']] ?></b> از ۱۰۰</div>
              <?php endif; ?>
              <div class="course-price-row">
                <div><?php if ($c['old_price'] > 0): ?><span class="price-old"><?= fmt_price($c['old_price']) ?></span><?php endif; ?><span class="price-now"><?= fmt_price($c['price']) ?></span></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
  document.getElementById('mobileFilterBtn')?.addEventListener('click', () => {
    document.getElementById('filterCard').classList.toggle('open');
  });
</script>
