<?php
/* 
   فرمول رتبه‌بندی نهایی از دو مرحله تشکیل شده است:

   مرحله ۱) میانگین بیزی برای دو معیار امتیازی (Bayesian Average)
   -----------------------------------------------------------------
   چون میانگین ساده امتیاز ناعادلانه است — مثلاً استادی با یک دوره و
   امتیاز ۵ نباید بالاتر از استادی با ۲۰ دوره و امتیاز ۴.۲ قرار بگیرد —
   از فرمول میانگین بیزی (همان فرمولی که IMDb برای رتبه‌بندی فیلم‌ها
   استفاده می‌کند) برای هر دو معیار زیر استفاده می‌شود:

       BayesianAvg = (v × R + m × C) / (v + m)

       v = تعداد نمونه (تعداد دوره، یا تعداد امتیازهای مستقیم دانشجویان)
       R = میانگین واقعی آن معیار برای استاد
       m = حداقل تعداد نمونه برای اعتماد کامل (ثابت سیستم)
       C = میانگین همان معیار در کل سایت (خط مبنا)

   این فرمول برای دو معیار جداگانه اعمال می‌شود:
     الف) میانگین امتیاز دوره‌های استاد (rating ثبت‌شده روی هر دوره)
     ب) میانگین امتیازی که دانشجویان مستقیماً به خود استاد داده‌اند

   مرحله ۲) نرمال‌سازی و ترکیب پنج معیار با وزن‌های مشخص
   -----------------------------------------------------------------
   پنج معیار واحدهای متفاوتی دارند و نمی‌توان مستقیماً جمعشان کرد.
   برای همین هرکدام به بازه ۰ تا ۱۰۰ نسبت به بالاترین مقدار بین همه
   اساتید نرمال می‌شوند (Min-Max Normalization با کف صفر)، سپس با
   وزن‌های زیر جمع می‌شوند:

       TeacherScore =
             (تعداد دوره نرمال‌شده        × 0.28)
           + (تعداد مقاله نرمال‌شده        × 0.28)
           + (امتیاز بیزی دوره‌ها نرمال‌شده  × 0.24)
           + (تعداد دانشجو نرمال‌شده        × 0.10)
           + (امتیاز بیزی مستقیم استاد نرمال‌شده × 0.10)

   منطق وزن‌دهی: سه معیار اول (جمعاً ۸۰٪) معیارهای اصلی تولید محتوا و
   کیفیت آموزشی هستند و پایه اصلی رتبه را می‌سازند. دو معیار جدید
   (تعداد دانشجو و امتیاز مستقیم دانشجویان) به‌عنوان سیگنال بازار و
   رضایت مستقیم با وزن کمتر (هرکدام ۱۰٪) اضافه می‌شوند — چون این دو
   می‌توانند تحت تاثیر عوامل بیرونی (قیمت، تازه‌بودن امتیازها) باشند
   و نباید هم‌وزن با کیفیت اثبات‌شده و پایدار محتوا باشند.

   نتیجه نهایی عددی بین ۰ تا ۱۰۰ است.
   ================================================================================= */

/** حداقل تعداد نمونه (دوره یا امتیاز مستقیم) برای اینکه میانگین یک استاد کامل قابل اعتماد باشد */
define('RANKING_BAYESIAN_M', 5);


function calculate_teacher_rankings(PDO $pdo): array {

    
    $globalAvgStmt = $pdo->query("SELECT AVG(rating) FROM courses WHERE status = 'approved'");
    $globalCourseAvg = (float) $globalAvgStmt->fetchColumn();
    if ($globalCourseAvg <= 0) {
        $globalCourseAvg = 4.5;
    }

    $globalTeacherRatingStmt = $pdo->query("SELECT AVG(rating) FROM teacher_ratings");
    $globalTeacherRatingAvg = (float) $globalTeacherRatingStmt->fetchColumn();
    if ($globalTeacherRatingAvg <= 0) {
        $globalTeacherRatingAvg = 4.5;
    }

  
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.username,
            u.bio,
            COUNT(DISTINCT c.id) AS course_count,
            COALESCE(AVG(c.rating), 0) AS avg_course_rating,
            COALESCE(SUM(c.students), 0) AS total_students,
            (SELECT COUNT(*) FROM articles a WHERE a.teacher_id = u.id AND a.status = 'approved') AS article_count,
            (SELECT COUNT(*) FROM teacher_ratings tr WHERE tr.teacher_id = u.id) AS rating_count,
            (SELECT COALESCE(AVG(tr.rating), 0) FROM teacher_ratings tr WHERE tr.teacher_id = u.id) AS avg_teacher_rating
        FROM users u
        LEFT JOIN courses c ON c.teacher_id = u.id AND c.status = 'approved'
        WHERE u.role = 'teacher' AND u.status = 'approved'
        GROUP BY u.id, u.name, u.username, u.bio
    ");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($teachers)) {
        return [];
    }

    // ۳) محاسبه دو امتیاز بیزی برای هر استاد (مرحله ۱ فرمول)
    $m = RANKING_BAYESIAN_M;
    foreach ($teachers as &$t) {
        $courseCount = (int) $t['course_count'];
        $courseAvg = (float) $t['avg_course_rating'];
        $t['bayesian_course_rating'] = $courseCount === 0
            ? $globalCourseAvg
            : (($courseCount * $courseAvg) + ($m * $globalCourseAvg)) / ($courseCount + $m);

        $ratingCount = (int) $t['rating_count'];
        $teacherRatingAvg = (float) $t['avg_teacher_rating'];
        $t['bayesian_teacher_rating'] = $ratingCount === 0
            ? $globalTeacherRatingAvg
            : (($ratingCount * $teacherRatingAvg) + ($m * $globalTeacherRatingAvg)) / ($ratingCount + $m);
    }
    unset($t);

    // ۴) پیدا کردن بیشینه هر معیار بین همه اساتید، برای نرمال‌سازی (مرحله ۲ فرمول)
    $maxCourses = max(array_column($teachers, 'course_count')) ?: 1;
    $maxArticles = max(array_column($teachers, 'article_count')) ?: 1;
    $maxCourseBayesian = max(array_column($teachers, 'bayesian_course_rating')) ?: 1;
    $maxStudents = max(array_column($teachers, 'total_students')) ?: 1;
    $maxTeacherBayesian = max(array_column($teachers, 'bayesian_teacher_rating')) ?: 1;

    // ۵) نرمال‌سازی هر معیار به بازه ۰ تا ۱۰۰ و ترکیب با وزن‌های نهایی
    foreach ($teachers as &$t) {
        $normCourses        = ($t['course_count']            / $maxCourses)         * 100;
        $normArticles       = ($t['article_count']           / $maxArticles)        * 100;
        $normCourseRating   = ($t['bayesian_course_rating']  / $maxCourseBayesian)  * 100;
        $normStudents       = ($t['total_students']           / $maxStudents)        * 100;
        $normTeacherRating  = ($t['bayesian_teacher_rating'] / $maxTeacherBayesian) * 100;

        $t['norm_courses'] = round($normCourses, 1);
        $t['norm_articles'] = round($normArticles, 1);
        $t['norm_course_rating'] = round($normCourseRating, 1);
        $t['norm_students'] = round($normStudents, 1);
        $t['norm_teacher_rating'] = round($normTeacherRating, 1);

        $finalScore = ($normCourses * 0.28)
                    + ($normArticles * 0.28)
                    + ($normCourseRating * 0.24)
                    + ($normStudents * 0.10)
                    + ($normTeacherRating * 0.10);

        $t['final_score'] = round($finalScore, 2);
        $t['bayesian_course_rating'] = round($t['bayesian_course_rating'], 2);
        $t['bayesian_teacher_rating'] = round($t['bayesian_teacher_rating'], 2);
        $t['avg_course_rating'] = round((float) $t['avg_course_rating'], 2);
        $t['avg_teacher_rating'] = round((float) $t['avg_teacher_rating'], 2);


        $t['bayesian_rating'] = $t['bayesian_course_rating'];
    }
    unset($t);

    // ۶) مرتب‌سازی نزولی بر اساس امتیاز نهایی — بالاترین امتیاز، رتبه ۱
    usort($teachers, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

    // ۷) اضافه کردن شماره رتبه به هر استاد
    foreach ($teachers as $i => &$t) {
        $t['rank'] = $i + 1;
    }
    unset($t);

    return $teachers;
}

/** گرفتن رتبه و امتیاز یک استاد خاص (برای نمایش در پنل خودش)*/
function get_teacher_rank(PDO $pdo, int $teacherId): ?array {
    $all = calculate_teacher_rankings($pdo);
    foreach ($all as $t) {
        if ((int) $t['id'] === $teacherId) {
            return $t;
        }
    }
    return null;
}

/** بررسی اینکه آیا یک دانشجو مجاز به امتیازدهی به یک استاد است یا نه(باید حداقل در یک دوره تاییدشده آن استاد ثبت‌نام کرده باشد)*/
function can_student_rate_teacher(PDO $pdo, int $studentId, int $teacherId): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        WHERE e.user_id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$studentId, $teacherId]);
    return ((int) $stmt->fetchColumn()) > 0;
}
