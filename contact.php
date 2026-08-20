<?php
$page_title = 'تماس با ما';
$base_url = '';
$asset_prefix = '';
$extra_css = ['contact'];
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (mb_strlen($old['name']) < 2) $errors['name'] = 'لطفاً نام خود را وارد کنید.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'لطفاً یک ایمیل معتبر وارد کنید.';
    if (mb_strlen($old['subject']) < 2) $errors['subject'] = 'لطفاً موضوع پیام را بنویسید.';
    if (mb_strlen($old['message']) < 4) $errors['message'] = 'لطفاً متن پیام را بنویسید.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
        $success = true;
        $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">خانه</a> / تماس با ما</div>
    <h1>تماس با ما</h1>
    <p>سوالی دارید؟ خوشحال می‌شویم به شما کمک کنیم.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-layout">
      <div class="contact-info-card reveal">
        <h3>راه‌های ارتباطی</h3>
        <div class="info-row"><span class="ic">📍</span><div><b>آدرس</b><span>تهران، خیابان آزادی، نرسیده به میدان آزادی</span></div></div>
        <div class="info-row"><span class="ic">📞</span><div><b>تلفن</b><span>۰۲۱-۱۲۳۴۵۶۷۸</span></div></div>
        <div class="info-row"><span class="ic">✉️</span><div><b>ایمیل</b><span>info@danesh.ir</span></div></div>
        <div class="info-row u-border-bottom-none"><span class="ic">⏰</span><div><b>ساعت پاسخگویی</b><span>شنبه تا چهارشنبه، ۹ تا ۱۸</span></div></div>
        <div class="social-row u-mt-1">
          <a href="#" class="u-social-light"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="u-social-light"><i class="fa-brands fa-telegram"></i></a>
          <a href="#" class="u-social-light"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="contact-form-card reveal delay-1">
        <?php if ($success): ?>
        <div class="success-banner show">پیام شما با موفقیت ارسال شد. به‌زودی با شما تماس خواهیم گرفت. ✓</div>
        <?php endif; ?>
        <form method="post" novalidate>
          <div class="form-row-2">
            <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
              <label for="cName">نام و نام خانوادگی</label>
              <input type="text" id="cName" name="name" value="<?= h($old['name']) ?>">
              <span class="field-error"><?= h($errors['name'] ?? '') ?></span>
            </div>
            <div class="field <?= isset($errors['email']) ? 'has-error' : '' ?>">
              <label for="cEmail">ایمیل</label>
              <input type="email" id="cEmail" name="email" value="<?= h($old['email']) ?>">
              <span class="field-error"><?= h($errors['email'] ?? '') ?></span>
            </div>
          </div>
          <div class="field <?= isset($errors['subject']) ? 'has-error' : '' ?>">
            <label for="cSubject">موضوع</label>
            <input type="text" id="cSubject" name="subject" value="<?= h($old['subject']) ?>">
            <span class="field-error"><?= h($errors['subject'] ?? '') ?></span>
          </div>
          <div class="field <?= isset($errors['message']) ? 'has-error' : '' ?>">
            <label for="cMessage">پیام شما</label>
            <textarea id="cMessage" name="message"><?= h($old['message']) ?></textarea>
            <span class="field-error"><?= h($errors['message'] ?? '') ?></span>
          </div>
          <button type="submit" class="btn btn-primary btn-block">ارسال پیام</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
