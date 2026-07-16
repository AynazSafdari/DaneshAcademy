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

$errors = [];
$old = ['first' => '', 'last' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['first'] = trim($_POST['first'] ?? '');
    $old['last'] = trim($_POST['last'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
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
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'student')");
            $stmt->execute([$fullName, $old['email'], $hash]);

            $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $fullName, 'username' => $old['email'], 'role' => 'student'];
            header("Location: index.php");
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card u-max-w-480">
      <div class="mark">آ</div>
      <h1>ساخت حساب کاربری</h1>
      <p class="subtitle">رایگان ثبت‌نام کنید و یادگیری را شروع کنید.</p>
      <form method="post" novalidate>
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
        <button type="submit" class="btn btn-primary btn-block">ثبت‌نام</button>
      </form>
      <div class="auth-switch">حساب کاربری دارید؟ <a href="login.php">وارد شوید</a></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
