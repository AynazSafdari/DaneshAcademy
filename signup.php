<?php
$page_title = 'ثبت‌نام';
$base_url = '';
$asset_prefix = '';
$extra_css = ['auth'];
$body_class = 'ledger-bg';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}


function safe_redirect_path(?string $path): string {
    if (!$path) return 'index.php';

    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, '\\\\')) {
        return 'index.php';
    }

    if (!preg_match('#^[a-zA-Z0-9_\-./?=&%]+$#', $path)) {
        return 'index.php';
    }
    return $path;
}

$redirectTarget = safe_redirect_path($_GET['redirect'] ?? $_POST['redirect'] ?? null);

$errors = [];
$old = ['first' => '', 'last' => '', 'email' => '', 'role' => 'student', 'bio' => ''];
$teacherPendingSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['first'] = trim($_POST['first'] ?? '');
    $old['last'] = trim($_POST['last'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['role'] = in_array($_POST['role'] ?? '', ['student', 'teacher']) ? $_POST['role'] : 'student';
    $old['bio'] = trim($_POST['bio'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($old['first'] === '') $errors['first'] = 'نام را وارد کنید.';
    if ($old['last'] === '') $errors['last'] = 'نام خانوادگی را وارد کنید.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'یک ایمیل معتبر وارد کنید.';
    if (mb_strlen($pass) < 6) $errors['password'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    if ($pass2 !== $pass || mb_strlen($pass2) < 6) $errors['password2'] = 'رمز عبور با تکرار آن یکسان نیست.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors['email'] = 'این ایمیل قبلاً ثبت‌نام کرده است.';
        } else {
            $fullName = $old['first'] . ' ' . $old['last'];
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            if ($old['role'] === 'teacher') {
                // --- ثبت‌نام استاد: نیاز به تایید ادمین دارد، ورود خودکار انجام نمی‌شود ---
                $bioToSave = $old['bio'] !== '' ? $old['bio'] : null;
                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role, status, bio) VALUES (?, ?, ?, 'teacher', 'pending', ?)");
                $stmt->execute([$fullName, $old['email'], $hash, $bioToSave]);

                $teacherPendingSuccess = true;
                $old = ['first' => '', 'last' => '', 'email' => '', 'role' => 'student', 'bio' => ''];
            } else {
                // --- ثبت‌نام دانشجو: بلافاصله وارد می‌شود ---
                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role, status) VALUES (?, ?, ?, 'student', 'approved')");
                $stmt->execute([$fullName, $old['email'], $hash]);

                $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $fullName, 'username' => $old['email'], 'role' => 'student'];
                header("Location: $redirectTarget");
                exit;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card u-max-w-480">
      <div class="mark">آ</div>
      <?php if ($teacherPendingSuccess): ?>
      <h1>درخواست شما ثبت شد</h1>
      <p class="subtitle">حساب استادی شما در انتظار بررسی و تایید مدیر سایت است.</p>
      <div class="pending-approval-box">
        <span class="pending-approval-icon">⏳</span>
        <p>پس از تایید توسط مدیر سایت، می‌توانید با همین ایمیل و رمز عبور از صفحه <a href="teacher/login.php" class="u-color-pine">ورود استاد</a> وارد پنل خود شوید. این فرآیند معمولاً کمتر از یک روز کاری زمان می‌برد.</p>
      </div>
      <a href="index.php" class="btn btn-outline btn-block u-mt-1">بازگشت به صفحه اصلی</a>
      <?php else: ?>
      <h1>ساخت حساب کاربری</h1>
      <p class="subtitle">رایگان ثبت‌نام کنید و یادگیری را شروع کنید.</p>
      <form method="post" novalidate>
        <input type="hidden" name="redirect" value="<?= h($redirectTarget) ?>">
        <div class="field">
          <label>ثبت‌نام به‌عنوان</label>
          <div class="role-select-row">
            <label class="role-option <?= $old['role'] === 'student' ? 'active' : '' ?>">
              <input type="radio" name="role" value="student" <?= $old['role'] === 'student' ? 'checked' : '' ?>>
              <span>🎓 دانشجو</span>
            </label>
            <label class="role-option <?= $old['role'] === 'teacher' ? 'active' : '' ?>">
              <input type="radio" name="role" value="teacher" <?= $old['role'] === 'teacher' ? 'checked' : '' ?>>
              <span>🧑 استاد</span>
            </label>
          </div>
          <?php if ($old['role'] === 'teacher'): ?>
          <p class="field-hint">ثبت‌نام استاد نیاز به بررسی و تایید مدیر سایت دارد و بلافاصله فعال نمی‌شود.</p>
          <?php endif; ?>
        </div>
        <div class="form-row-2">
          <div class="field <?= isset($errors['first']) ? 'has-error' : '' ?>">
            <label for="sFirst">نام</label>
            <input type="text" id="sFirst" name="first" value="<?= h($old['first']) ?>">
            <span class="field-error"><?= h($errors['first'] ?? '') ?></span>
          </div>
          <div class="field <?= isset($errors['last']) ? 'has-error' : '' ?>">
            <label for="sLast">نام خانوادگی</label>
            <input type="text" id="sLast" name="last" value="<?= h($old['last']) ?>">
            <span class="field-error"><?= h($errors['last'] ?? '') ?></span>
          </div>
        </div>
        <div class="field <?= isset($errors['email']) ? 'has-error' : '' ?>">
          <label for="sEmail">ایمیل</label>
          <input type="email" id="sEmail" name="email" value="<?= h($old['email']) ?>">
          <span class="field-error"><?= h($errors['email'] ?? '') ?></span>
        </div>
        <div class="field <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label for="sPass">رمز عبور</label>
          <input type="password" id="sPass" name="password">
          <span class="field-hint">حداقل ۶ کاراکتر</span>
          <span class="field-error"><?= h($errors['password'] ?? '') ?></span>
        </div>
        <div class="field <?= isset($errors['password2']) ? 'has-error' : '' ?>">
          <label for="sPass2">تکرار رمز عبور</label>
          <input type="password" id="sPass2" name="password2">
          <span class="field-error"><?= h($errors['password2'] ?? '') ?></span>
        </div>
        <div class="field <?= $old['role'] === 'teacher' ? '' : 'u-hidden' ?>" id="bioField">
          <label for="sBio">بیوگرافی کوتاه (اختیاری، در پروفایل عمومی شما نمایش داده می‌شود)</label>
          <textarea id="sBio" name="bio" placeholder="مثال: مدرس برنامه‌نویسی وب با ۵ سال سابقه صنعتی."><?= h($old['bio']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">ثبت‌نام</button>
      </form>
      <div class="auth-switch">حساب کاربری دارید؟ <a href="login.php">وارد شوید</a></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
  document.querySelectorAll('.role-option input[name="role"]').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
      radio.closest('.role-option').classList.add('active');

      const bioField = document.getElementById('bioField');
      if (radio.value === 'teacher') {
        bioField.classList.remove('u-hidden');
      } else {
        bioField.classList.add('u-hidden');
      }
    });
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
