<?php
$page_title = 'تنظیمات پروفایل';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_teacher('login.php');

$teacherId = (int) $_SESSION['user']['id'];
$message = null;
$messageType = 'success';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$teacherId]);
$me = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->execute([$bio !== '' ? $bio : null, $teacherId]);
    $message = 'بیوگرافی شما با موفقیت به‌روزرسانی شد.';
    $me['bio'] = $bio;
}

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner banner-success"><?= h($message) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-head"><h3>ویرایش بیوگرافی عمومی</h3></div>
  <div class="modal-body">
    <p class="u-text-sm-soft u-mb-1">این متن در صفحه عمومی پروفایل شما (که دانشجویان می‌بینند) نمایش داده می‌شود.</p>
    <form method="post">
      <div class="field">
        <label for="bio">بیوگرافی کوتاه</label>
        <textarea id="bio" name="bio" placeholder="مثال: مدرس برنامه‌نویسی وب با ۵ سال سابقه صنعتی."><?= h($me['bio'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
    </form>
    <a href="../teacher-profile.php?id=<?= $teacherId ?>" class="btn btn-ghost u-mt-1">مشاهده پروفایل عمومی من ←</a>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
