<?php

$hostname = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "mehrabad";

date_default_timezone_set("Asia/Tehran");

try {
    $pdo = new PDO(
        "mysql:host=$hostname;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    file_put_contents(
        __DIR__ . "/../logs/log.txt",
        date("Y/m/d H:i:s") . " ===> " . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
    die("خطا در اتصال به پایگاه داده. لطفاً بعداً تلاش کنید.");
}
