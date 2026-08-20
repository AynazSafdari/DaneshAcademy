<?php

session_start();

function fmt_price($n) {
    $n = (float) $n;
    if ($n == 0) return 'رایگان';
    return number_format($n, 0, '.', ',') . ' تومان';
}

function fmt_date($datetime) {
    $months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
    $ts = strtotime($datetime);
    return date("Y/m/d", $ts);
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

function is_teacher() {
    return is_logged_in() && $_SESSION['user']['role'] === 'teacher';
}

function require_admin($redirect = 'login.php') {
    if (!is_admin()) {
        header("Location: $redirect");
        exit;
    }
}

function require_teacher($redirect = 'login.php') {
    global $pdo;
    if (!is_teacher()) {
        header("Location: $redirect");
        exit;
    }
    // بررسی مجدد وضعیت از دیتابیس؛ اگر ادمین بعد از ورود استاد، وضعیتش را تغییر داده باشد
    // (مثلاً به pending یا rejected)، جلسه او بلافاصله باطل می‌شود
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'teacher'");
    $stmt->execute([$_SESSION['user']['id']]);
    $currentStatus = $stmt->fetchColumn();
    if ($currentStatus !== 'approved') {
        unset($_SESSION['user']);
        header("Location: $redirect");
        exit;
    }
}

function require_login($redirect = '../login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}


// image
function handle_image_upload($field, $targetDir, $oldImage = null) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // فایلی انتخاب نشده؛ تصویر قبلی حفظ می‌شود
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return null;
    }

    $rootDir = dirname(__DIR__);
    $fullTargetDir = $rootDir . '/' . rtrim($targetDir, '/') . '/';

    if (!is_dir($fullTargetDir)) {
        mkdir($fullTargetDir, 0755, true);
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $destination = $fullTargetDir . $newName;

    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
        // حذف تصویر قبلی در صورت وجود و متفاوت بودن از پیش‌فرض
        if ($oldImage && strpos($oldImage, 'default-') === false) {
            $oldFullPath = $rootDir . '/' . $oldImage;
            if (file_exists($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }
        return rtrim($targetDir, '/') . '/' . $newName;
    }

    return null;
}

function flash_set($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function flash_get($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}


// bookmark
function is_bookmarked(PDO $pdo, int $userId, string $contentType, int $contentId): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ? AND content_type = ? AND content_id = ?");
    $stmt->execute([$userId, $contentType, $contentId]);
    return ((int) $stmt->fetchColumn()) > 0;
}


function toggle_bookmark(PDO $pdo, int $userId, string $contentType, int $contentId): bool {
    if (is_bookmarked($pdo, $userId, $contentType, $contentId)) {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND content_type = ? AND content_id = ?");
        $stmt->execute([$userId, $contentType, $contentId]);
        return false;
    }
    $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, content_type, content_id) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $contentType, $contentId]);
    return true;
}
