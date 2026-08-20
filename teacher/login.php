<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_teacher()) {
    header("Location: dashboard.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'teacher'");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch();

    if ($teacher && password_verify($password, $teacher['password'])) {
        if ($teacher['status'] === 'pending') {
            $error = 'حساب شما هنوز توسط مدیر سایت تایید نشده است. لطفاً منتظر بمانید.';
        } elseif ($teacher['status'] === 'rejected') {
            $error = 'متاسفانه درخواست ثبت‌نام شما تایید نشد. برای اطلاعات بیشتر با پشتیبانی تماس بگیرید.';
        } else {
            $_SESSION['user'] = ['id' => $teacher['id'], 'name' => $teacher['name'], 'username' => $teacher['username'], 'role' => 'teacher'];
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود استاد | آکادمی دانش</title>
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/Desktop/admin-login.css">
<link rel="stylesheet" href="../assets/css/Tablet/admin-login.css">
<link rel="stylesheet" href="../assets/css/Mobile/admin-login.css">
</head>
<body>

<div class="admin-login-card">
  <div class="mark">آ</div>
  <h1>ورود استاد</h1>
  <p>برای دسترسی به پنل استاد، اطلاعات خود را وارد کنید.</p>
  <form method="post" novalidate>
    <div class="field <?= $error ? 'has-error' : '' ?>">
      <label for="tUser">نام کاربری (ایمیل)</label>
      <input type="text" id="tUser" name="username" autocomplete="username" value="<?= h($_POST['username'] ?? '') ?>">
    </div>
    <div class="field <?= $error ? 'has-error' : '' ?>">
      <label for="tPass">رمز عبور</label>
      <input type="password" id="tPass" name="password" autocomplete="current-password">
      <span class="field-error"><?= h($error ?? '') ?></span>
    </div>
    <button type="submit" class="btn btn-primary btn-block">ورود به پنل استاد</button>
  </form>
  <a href="../signup.php" class="back-link">حساب استاد ندارید؟ ثبت‌نام کنید</a>
  <a href="../index.php" class="back-link">← بازگشت به سایت</a>
</div>

</body>
</html>
