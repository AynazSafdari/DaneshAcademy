<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'requiresAuth' => true,
        'message' => 'برای نشان‌کردن، ابتدا وارد حساب دانشجویی خود شوید یا ثبت‌نام کنید.'
    ]);
    exit;
}

$contentType = $_POST['type'] ?? '';
$contentId = (int) ($_POST['id'] ?? 0);

if (!in_array($contentType, ['course', 'article']) || $contentId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'درخواست نامعتبر است.']);
    exit;
}

$studentId = (int) $_SESSION['user']['id'];
$nowBookmarked = toggle_bookmark($pdo, $studentId, $contentType, $contentId);

echo json_encode(['ok' => true, 'bookmarked' => $nowBookmarked]);
