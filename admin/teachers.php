<?php
$page_title = 'اساتید و تایید محتوا';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ranking.php';
require_admin('login.php');

$tab = $_GET['tab'] ?? 'ranking';
$message = null;
$messageType = 'success';

/*  تایید / رد یک دوره یا مقاله (از تب‌های pending) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_action'])) {
    $type = $_POST['content_type'] ?? '';   // 'course', 'article' یا 'teacher'
    $id = (int) ($_POST['content_id'] ?? 0);
    $decision = $_POST['approve_action'] === 'approve' ? 'approved' : 'rejected';

    if ($type === 'course' && $id > 0) {
        $pdo->prepare("UPDATE courses SET status = ? WHERE id = ?")->execute([$decision, $id]);
        $message = $decision === 'approved' ? 'دوره تایید و در سایت منتشر شد.' : 'دوره رد شد.';
    } elseif ($type === 'article' && $id > 0) {
        $pdo->prepare("UPDATE articles SET status = ? WHERE id = ?")->execute([$decision, $id]);
        $message = $decision === 'approved' ? 'مقاله تایید و در سایت منتشر شد.' : 'مقاله رد شد.';
    } elseif ($type === 'teacher' && $id > 0) {
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'teacher'")->execute([$decision, $id]);
        $message = $decision === 'approved' ? 'حساب استاد تایید شد و اکنون می‌تواند وارد پنل خود شود.' : 'درخواست ثبت‌نام این استاد رد شد.';
    }
    $tab = $_POST['return_tab'] ?? 'pending-courses';
}

/*  داده مورد نیاز هر تب */
$rankings = [];
$pendingCourses = [];
$pendingArticles = [];
$pendingTeachers = [];

if ($tab === 'ranking') {
    $rankings = calculate_teacher_rankings($pdo);
} elseif ($tab === 'pending-courses') {
    $pendingCourses = $pdo->query("
        SELECT c.*, cat.name AS category_name
        FROM courses c
        JOIN categories cat ON cat.id = c.category_id
        WHERE c.status = 'pending'
        ORDER BY c.created_at ASC
    ")->fetchAll();
} elseif ($tab === 'pending-articles') {
    $pendingArticles = $pdo->query("
        SELECT * FROM articles WHERE status = 'pending' ORDER BY created_at ASC
    ")->fetchAll();
} elseif ($tab === 'pending-teachers') {
    $pendingTeachers = $pdo->query("
        SELECT * FROM users WHERE role = 'teacher' AND status = 'pending' ORDER BY created_at ASC
    ")->fetchAll();
}

$pendingCoursesCount = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status='pending'")->fetchColumn();
$pendingArticlesCount = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status='pending'")->fetchColumn();
$pendingTeachersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending'")->fetchColumn();

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner <?= $messageType === 'danger' ? 'banner-danger' : 'banner-success' ?>">
  <?= h($message) ?>
</div>
<?php endif; ?>

<div class="teacher-tabs">
  <a href="teachers.php?tab=ranking" class="teacher-tab <?= $tab === 'ranking' ? 'active' : '' ?>">🏆 رتبه‌بندی اساتید</a>
  <a href="teachers.php?tab=pending-teachers" class="teacher-tab <?= $tab === 'pending-teachers' ? 'active' : '' ?>">
    🧑 ثبت‌نام‌های در انتظار تایید
    <?php if ($pendingTeachersCount > 0): ?><span class="nav-pending-badge"><?= $pendingTeachersCount ?></span><?php endif; ?>
  </a>
  <a href="teachers.php?tab=pending-courses" class="teacher-tab <?= $tab === 'pending-courses' ? 'active' : '' ?>">
    🎓 دوره‌های در انتظار تایید
    <?php if ($pendingCoursesCount > 0): ?><span class="nav-pending-badge"><?= $pendingCoursesCount ?></span><?php endif; ?>
  </a>
  <a href="teachers.php?tab=pending-articles" class="teacher-tab <?= $tab === 'pending-articles' ? 'active' : '' ?>">
    📝 مقالات در انتظار تایید
    <?php if ($pendingArticlesCount > 0): ?><span class="nav-pending-badge"><?= $pendingArticlesCount ?></span><?php endif; ?>
  </a>
</div>

<?php if ($tab === 'ranking'): ?>
<!--  تب رتبه‌بندی اساتید -->


<div class="admin-card">
  <div class="admin-card-head"><h3>جدول رتبه‌بندی (<?= count($rankings) ?> استاد)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>رتبه</th><th>استاد</th><th>دوره</th><th>مقاله</th><th>دانشجو</th><th>امتیاز دوره‌ها</th><th>امتیاز مستقیم دانشجویان</th><th>امتیاز نهایی</th></tr>
      </thead>
      <tbody>
        <?php if (empty($rankings)): ?>
        <tr class="empty-row"><td colspan="8">هنوز هیچ استادی در سیستم ثبت نشده است.</td></tr>
        <?php else: foreach ($rankings as $t): ?>
        <tr>
          <td><span class="rank-badge <?= $t['rank'] <= 3 ? 'rank-' . $t['rank'] : '' ?>">#<?= $t['rank'] ?></span></td>
          <td><strong class="u-color-pine"><?= h($t['name']) ?></strong><br><span class="u-text-sm-soft"><?= h($t['username']) ?></span></td>
          <td><?= (int)$t['course_count'] ?></td>
          <td><?= (int)$t['article_count'] ?></td>
          <td><?= number_format($t['total_students']) ?></td>
          <td>★ <?= $t['bayesian_course_rating'] ?><?php if ($t['course_count'] === 0): ?><span class="u-text-sm-soft"> (بدون دوره)</span><?php endif; ?></td>
          <td>★ <?= $t['bayesian_teacher_rating'] ?><span class="u-text-sm-soft"> (<?= (int)$t['rating_count'] ?> نظر)</span></td>
          <td><strong class="u-color-pine"><?= $t['final_score'] ?></strong> / ۱۰۰</td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif ($tab === 'pending-teachers'): ?>
<!--  تب ثبت‌نام‌های استاد در انتظار تایید -->
<div class="admin-card">
  <div class="admin-card-head"><h3>ثبت‌نام‌های در انتظار تایید (<?= count($pendingTeachers) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>نام</th><th>ایمیل</th><th>بیوگرافی</th><th>تاریخ درخواست</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($pendingTeachers)): ?>
        <tr class="empty-row"><td colspan="5">هیچ درخواست ثبت‌نام استادی در انتظار تایید نیست.</td></tr>
        <?php else: foreach ($pendingTeachers as $t): ?>
        <tr>
          <td><strong class="u-color-pine"><?= h($t['name']) ?></strong></td>
          <td><?= h($t['username']) ?></td>
          <td><span class="u-text-sm-soft"><?= h($t['bio'] ?: '—') ?></span></td>
          <td><?= fmt_date($t['created_at']) ?></td>
          <td>
            <div class="row-actions">
              <form method="post" class="u-inline-form">
                <input type="hidden" name="content_type" value="teacher">
                <input type="hidden" name="content_id" value="<?= $t['id'] ?>">
                <input type="hidden" name="return_tab" value="pending-teachers">
                <button type="submit" name="approve_action" value="approve" class="icon-btn" title="تایید">✅</button>
                <button type="submit" name="approve_action" value="reject" class="icon-btn danger" title="رد">❌</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif ($tab === 'pending-courses'): ?>
<!--  تب دوره‌های در انتظار تایید -->
<div class="admin-card">
  <div class="admin-card-head"><h3>دوره‌های در انتظار تایید (<?= count($pendingCourses) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>دوره</th><th>استاد</th><th>دسته‌بندی</th><th>سطح</th><th>قیمت</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($pendingCourses)): ?>
        <tr class="empty-row"><td colspan="6">هیچ دوره‌ای در انتظار تایید نیست.</td></tr>
        <?php else: foreach ($pendingCourses as $c): ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($c['image']) ?>" alt="">
              <span class="title"><?= h($c['title']) ?></span>
            </div>
          </td>
          <td><?= h($c['instructor']) ?></td>
          <td><?= h($c['category_name']) ?></td>
          <td><span class="badge-level"><?= h($c['level']) ?></span></td>
          <td><?= fmt_price($c['price']) ?></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="مشاهده کامل" href="courses.php?action=edit&edit=<?= $c['id'] ?>">👁️</a>
              <form method="post" class="u-inline-form">
                <input type="hidden" name="content_type" value="course">
                <input type="hidden" name="content_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="return_tab" value="pending-courses">
                <button type="submit" name="approve_action" value="approve" class="icon-btn" title="تایید">✅</button>
                <button type="submit" name="approve_action" value="reject" class="icon-btn danger" title="رد">❌</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif ($tab === 'pending-articles'): ?>
<!--  تب مقالات در انتظار تایید -->
<div class="admin-card">
  <div class="admin-card-head"><h3>مقالات در انتظار تایید (<?= count($pendingArticles) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>مقاله</th><th>نویسنده</th><th>دسته‌بندی</th><th>تاریخ ثبت</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($pendingArticles)): ?>
        <tr class="empty-row"><td colspan="5">هیچ مقاله‌ای در انتظار تایید نیست.</td></tr>
        <?php else: foreach ($pendingArticles as $a): ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($a['image']) ?>" alt="">
              <span class="title"><?= h($a['title']) ?></span>
            </div>
          </td>
          <td><?= h($a['author']) ?></td>
          <td><span class="tag"><?= h($a['category']) ?></span></td>
          <td><?= fmt_date($a['created_at']) ?></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="مشاهده کامل" href="articles.php?action=edit&edit=<?= $a['id'] ?>">👁️</a>
              <form method="post" class="u-inline-form">
                <input type="hidden" name="content_type" value="article">
                <input type="hidden" name="content_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="return_tab" value="pending-articles">
                <button type="submit" name="approve_action" value="approve" class="icon-btn" title="تایید">✅</button>
                <button type="submit" name="approve_action" value="reject" class="icon-btn danger" title="رد">❌</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes_footer.php'; ?>
