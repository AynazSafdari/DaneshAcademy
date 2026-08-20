<?php
$page_title = 'ورود';
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
$old = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($old['username'] === '') $errors['username'] = 'لطفاً نام کاربری یا ایمیل را وارد کنید.';
    if ($password === '') $errors['password'] = 'لطفاً رمز عبور را وارد کنید.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'student'");
        $stmt->execute([$old['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'username' => $user['username'], 'role' => 'student'];
            header("Location: index.php");
            exit;
        } else {
            $errors['password'] = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card">
      <div class="mark">آ</div>
      <h1>ورود به حساب کاربری</h1>
      <p class="subtitle">خوش برگشتید! اطلاعات خود را وارد کنید.</p>
      <form method="post" novalidate>
        <div class="field <?= isset($errors['username']) ? 'has-error' : '' ?>">
          <label for="lUser">نام کاربری یا ایمیل</label>
          <input type="text" id="lUser" name="username" value="<?= h($old['username']) ?>">
          <span class="field-error"><?= h($errors['username'] ?? '') ?></span>
        </div>
        <div class="field <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label for="lPass">رمز عبور</label>
          <input type="password" id="lPass" name="password">
          <span class="field-error"><?= h($errors['password'] ?? '') ?></span>
        </div>
        <button type="submit" class="btn btn-primary btn-block">ورود</button>
      </form>
      <div class="auth-switch">حساب کاربری ندارید؟ <a href="signup.php">ثبت‌نام کنید</a></div>
      <div class="demo-hint">استاد هستید؟ از <a href="teacher/login.php">صفحه ورود استاد</a> استفاده کنید.</div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
