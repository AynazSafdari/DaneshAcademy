<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user'] = ['id' => $admin['id'], 'name' => $admin['name'], 'username' => $admin['username'], 'role' => 'admin'];
        header("Location: dashboard.php");
        exit;
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
<title>ورود مدیر | آکادمی دانش</title>
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/Desktop/admin-login.css">
<link rel="stylesheet" href="../assets/css/Tablet/admin-login.css">
<link rel="stylesheet" href="../assets/css/Mobile/admin-login.css">
</head>
<body>

<div class="admin-login-card">
  <div class="mark">آ</div>
  <h1>ورود مدیر سامانه</h1>
  <p>برای دسترسی به پنل مدیریت، اطلاعات خود را وارد کنید.</p>
  <form method="post" novalidate>
    <div class="field <?= $error ? 'has-error' : '' ?>">
      <label for="aUser">نام کاربری</label>
      <input type="text" id="aUser" name="username" autocomplete="username" value="<?= h($_POST['username'] ?? '') ?>">
    </div>
    <div class="field <?= $error ? 'has-error' : '' ?>">
      <label for="aPass">رمز عبور</label>
      <input type="password" id="aPass" name="password" autocomplete="current-password">
      <span class="field-error"><?= h($error ?? '') ?></span>
    </div>
    <button type="submit" class="btn btn-primary btn-block">ورود به پنل مدیریت</button>
  </form>
  <a href="../index.php" class="back-link">← بازگشت به سایت</a>
</div>

</body>
</html>
