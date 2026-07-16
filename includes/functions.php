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

function require_admin($redirect = 'login.php') {
    if (!is_admin()) {
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

/**
 * آپلود تصویر و بازگرداندن مسیر نسبی ذخیره‌شده
 * @param string $field نام فیلد فرم (مثلاً 'image')
 * @param string $targetDir پوشه مقصد نسبت به ریشه سایت (مثلاً 'images/courses/')
 * @param string|null $oldImage مسیر تصویر قبلی (برای حذف هنگام ویرایش)
 * @return string|null مسیر تصویر جدید، یا null اگر فایلی آپلود نشده
 */
function handle_image_upload($field, $targetDir, $oldImage = null) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; 
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
