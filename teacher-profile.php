<?php
$base_url = '';
$asset_prefix = '';
$extra_css = ['teacher-profile'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ranking.php';

$teacherId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'teacher' AND status = 'approved'");
$stmt->execute([$teacherId]);
$teacher = $stmt->fetch();

$page_title = $teacher ? $teacher['name'] : 'استاد یافت نشد';

$ratingMessage = null;
$ratingMessageType = 'success';
$canRate = false;
$existingRating = null;

if ($teacher && is_logged_in() && $_SESSION['user']['role'] === 'student') {
    $studentId = (int) $_SESSION['user']['id'];
    $canRate = can_student_rate_teacher($pdo, $studentId, $teacherId);

    if ($canRate) {
        $stmt = $pdo->prepare("SELECT * FROM teacher_ratings WHERE teacher_id = ? AND student_id = ?");
        $stmt->execute([$teacherId, $studentId]);
        $existingRating = $stmt->fetch();
    }

    // --- ثبت یا ویرایش امتیاز ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating']) && $canRate) {
        $ratingValue = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($ratingValue < 1 || $ratingValue > 5) {
            $ratingMessage = 'لطفاً یک امتیاز بین ۱ تا ۵ انتخاب کنید.';
            $ratingMessageType = 'danger';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO teacher_ratings (teacher_id, student_id, rating, comment)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = NOW()
            ");
            $stmt->execute([$teacherId, $studentId, $ratingValue, $comment !== '' ? $comment : null]);
            $ratingMessage = $existingRating ? 'امتیاز شما به‌روزرسانی شد.' : 'امتیاز شما با موفقیت ثبت شد. سپاسگزاریم!';

            $stmt = $pdo->prepare("SELECT * FROM teacher_ratings WHERE teacher_id = ? AND student_id = ?");
            $stmt->execute([$teacherId, $studentId]);
            $existingRating = $stmt->fetch();
        }
    }
}

if ($teacher) {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name AS category_name
        FROM courses c
        JOIN categories cat ON cat.id = c.category_id
        WHERE c.teacher_id = ? AND c.status = 'approved'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$teacherId]);
    $teacherCourses = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM articles WHERE teacher_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
    $teacherArticles = $stmt->fetchAll();

    $myRank = get_teacher_rank($pdo, $teacherId);

    $stmt = $pdo->prepare("
        SELECT tr.rating, tr.comment, tr.created_at, u.name AS student_name
        FROM teacher_ratings tr
        JOIN users u ON u.id = tr.student_id
        WHERE tr.teacher_id = ?
        ORDER BY tr.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$teacherId]);
    $recentRatings = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!$teacher): ?>
<section class="section u-text-center">
  <div class="container">
    <div class="u-text-2_6">🔍</div>
    <h2>استاد مورد نظر یافت نشد</h2>
    <a href="teachers.php" class="btn btn-primary u-mt-1">بازگشت به لیست اساتید</a>
  </div>
</section>
<?php else: ?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / <a href="teachers.php">اساتید</a> / <span><?= h($teacher['name']) ?></span></div>
    <div class="teacher-profile-hero-row">
      <div class="teacher-profile-avatar-lg"><?= h(mb_substr($teacher['name'], -1)) ?></div>
      <div>
        <h1><?= h($teacher['name']) ?></h1>
        <?php if (!empty($teacher['bio'])): ?>
        <p><?= h($teacher['bio']) ?></p>
        <?php endif; ?>
        <?php if ($myRank): ?>
        <div class="teacher-profile-rank-row">
          <span class="rank-badge <?= $myRank['rank'] <= 3 ? 'rank-' . $myRank['rank'] : '' ?>">#<?= $myRank['rank'] ?> در آکادمی دانش</span>
          <span>★ <?= $myRank['bayesian_teacher_rating'] ?> امتیاز دانشجویان (<?= (int)$myRank['rating_count'] ?> نظر)</span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section u-pt-0">
  <div class="container">
    <?php if ($myRank): ?>
    <div class="teacher-profile-stats-row reveal">
      <div><b><?= (int)$myRank['course_count'] ?></b><span>دوره فعال</span></div>
      <div><b><?= (int)$myRank['article_count'] ?></b><span>مقاله منتشرشده</span></div>
      <div><b><?= number_format($myRank['total_students']) ?></b><span>دانشجوی ثبت‌نامی</span></div>
      <div><b><?= $myRank['final_score'] ?></b><span>امتیاز کل از ۱۰۰</span></div>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section u-pt-0">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">دوره‌ها</p><h2>دوره‌های <?= h($teacher['name']) ?></h2></div>
    </div>
    <?php if (empty($teacherCourses)): ?>
    <p class="u-text-sm-soft">این استاد هنوز دوره تاییدشده‌ای منتشر نکرده است.</p>
    <?php else: ?>
    <div class="course-grid">
      <?php foreach ($teacherCourses as $c): ?>
      <div class="ledger-card reveal">
        <div class="punch-holes"><span></span><span></span><span></span></div>
        <div class="course-card-img">
          <img src="<?= h($c['image']) ?>" alt="<?= h($c['title']) ?>">
          <span class="badge-level"><?= h($c['level']) ?></span>
        </div>
        <div class="course-card-body">
          <div class="course-cat"><?= h($c['category_name']) ?></div>
          <a href="course-detail.php?id=<?= (int)$c['id'] ?>" class="course-title-link"><?= h($c['title']) ?></a>
          <div class="course-meta"><span class="star">★ <?= h($c['rating']) ?></span><span>· <?= number_format($c['students']) ?> دانشجو</span></div>
          <div class="course-price-row">
            <div><?php if ($c['old_price'] > 0): ?><span class="price-old"><?= fmt_price($c['old_price']) ?></span><?php endif; ?><span class="price-now"><?= fmt_price($c['price']) ?></span></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section u-bg-cream-soft">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">مقالات</p><h2>مقالات <?= h($teacher['name']) ?></h2></div>
    </div>
    <?php if (empty($teacherArticles)): ?>
    <p class="u-text-sm-soft">این استاد هنوز مقاله تاییدشده‌ای منتشر نکرده است.</p>
    <?php else: ?>
    <div class="article-row">
      <?php foreach ($teacherArticles as $a): ?>
      <div class="ledger-card reveal">
        <div class="punch-holes"><span></span><span></span><span></span></div>
        <div class="article-card-img"><img src="<?= h($a['image']) ?>" alt="<?= h($a['title']) ?>"></div>
        <div class="article-card-body">
          <span class="tag"><?= h($a['category']) ?></span>
          <h3><a href="article-detail.php?id=<?= (int)$a['id'] ?>" class="u-color-pine"><?= h($a['title']) ?></a></h3>
          <p><?= h($a['summary']) ?></p>
          <div class="article-card-meta"><span><?= fmt_date($a['created_at']) ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div><p class="eyebrow">نظرات دانشجویان</p><h2>امتیاز و نظرات درباره <?= h($teacher['name']) ?></h2></div>
    </div>

    <?php if (is_logged_in() && $_SESSION['user']['role'] === 'student'): ?>
      <?php if ($canRate): ?>
      <div class="rating-form-card reveal">
        <?php if ($ratingMessage): ?>
        <div class="admin-banner <?= $ratingMessageType === 'danger' ? 'banner-danger' : 'banner-success' ?>"><?= h($ratingMessage) ?></div>
        <?php endif; ?>
        <h3><?= $existingRating ? 'ویرایش امتیاز شما' : 'به این استاد امتیاز بدهید' ?></h3>
        <form method="post">
          <div class="star-rating-input" dir="ltr">
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= ($existingRating && (int)$existingRating['rating'] === $i) ? 'checked' : '' ?> required>
            <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
          <div class="field">
            <label for="comment">نظر شما (اختیاری)</label>
            <textarea id="comment" name="comment" placeholder="تجربه خودتان از این استاد را بنویسید..."><?= h($existingRating['comment'] ?? '') ?></textarea>
          </div>
          <button type="submit" name="submit_rating" value="1" class="btn btn-primary">ثبت امتیاز</button>
        </form>
      </div>
      <?php else: ?>
      <p class="u-text-sm-soft">برای امتیازدهی، باید حداقل در یکی از دوره‌های این استاد ثبت‌نام کرده باشید.</p>
      <?php endif; ?>
    <?php elseif (!is_logged_in()): ?>
      <p class="u-text-sm-soft"><a href="login.php" class="u-color-pine">وارد شوید</a> تا در صورت ثبت‌نام در دوره‌های این استاد، بتوانید امتیاز بدهید.</p>
    <?php endif; ?>

    <div class="teacher-reviews-list u-mt-1">
      <?php if (empty($recentRatings)): ?>
      <p class="u-text-sm-soft">هنوز نظری برای این استاد ثبت نشده است.</p>
      <?php else: foreach ($recentRatings as $r): ?>
      <div class="review-item reveal">
        <div class="review-item-top">
          <strong class="u-color-pine"><?= h($r['student_name']) ?></strong>
          <span class="star">★ <?= (int)$r['rating'] ?></span>
        </div>
        <?php if (!empty($r['comment'])): ?>
        <p><?= h($r['comment']) ?></p>
        <?php endif; ?>
        <span class="u-text-sm-soft"><?= fmt_date($r['created_at']) ?></span>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
