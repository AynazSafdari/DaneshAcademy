<?php
$page_title = 'مدیریت دوره‌ها';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$editId = (int) ($_GET['edit'] ?? 0);
$message = null;
$messageType = 'success';

// --- دریافت دسته‌بندی‌ها برای فرم ---
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

/*  CREATE / UPDATE  */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $instructor = trim($_POST['instructor'] ?? '');
    $level = $_POST['level'] ?? 'مقدماتی';
    $price = (float) ($_POST['price'] ?? 0);
    $oldPrice = (float) ($_POST['old_price'] ?? 0);
    $hours = (int) ($_POST['hours'] ?? 0);
    $lessons = (int) ($_POST['lessons'] ?? 0);
    $summary = trim($_POST['summary'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $syllabus = trim($_POST['syllabus'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;

    $errors = [];
    if ($title === '') $errors[] = 'عنوان دوره را وارد کنید.';
    if ($categoryId <= 0) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    if ($instructor === '') $errors[] = 'نام مدرس را وارد کنید.';
    if ($summary === '') $errors[] = 'توضیح کوتاه را وارد کنید.';
    if ($description === '') $errors[] = 'توضیحات کامل را وارد کنید.';

    // در حالت ویرایش، اگر عکس جدید آپلود نشود، عکس قبلی حفظ می‌شود
    $oldImage = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT image FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $oldImage = $stmt->fetchColumn();
    }

    if (empty($errors)) {
        $newImage = handle_image_upload('image', 'images/courses', $oldImage);
        $imageToSave = $newImage ?? $oldImage ?? 'images/courses/default-course.jpg';

        if ($id > 0) {
            // --- UPDATE ---
            $stmt = $pdo->prepare("
                UPDATE courses SET
                    title = ?, category_id = ?, instructor = ?, level = ?,
                    price = ?, old_price = ?, hours = ?, lessons = ?,
                    image = ?, summary = ?, description = ?, syllabus = ?, featured = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $categoryId, $instructor, $level, $price, $oldPrice, $hours, $lessons, $imageToSave, $summary, $description, $syllabus, $featured, $id]);
            $message = 'دوره با موفقیت ویرایش شد.';
        } else {
            // --- CREATE (دوره‌ای که مستقیم توسط ادمین ساخته می‌شود، فوراً approved است) ---
            $stmt = $pdo->prepare("
                INSERT INTO courses
                (title, category_id, instructor, status, level, price, old_price, hours, lessons, image, summary, description, syllabus, featured, rating, students)
                VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 4.5, 0)
            ");
            $stmt->execute([$title, $categoryId, $instructor, $level, $price, $oldPrice, $hours, $lessons, $imageToSave, $summary, $description, $syllabus, $featured]);
            $message = 'دوره جدید با موفقیت اضافه شد.';
        }
        $action = 'list';
        $editId = 0;
    } else {
        $message = implode(' ', $errors);
        $messageType = 'danger';
        $action = $id > 0 ? 'edit' : 'create';
        $editId = $id;
        // برای نمایش مقادیر واردشده در فرم پس از خطا
        $formData = $_POST;
    }
}

/*  DELETE */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT image FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();

    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);

    if ($img && strpos($img, 'default-') === false) {
        $fullPath = __DIR__ . '/../' . $img;
        if (file_exists($fullPath)) @unlink($fullPath);
    }
    $message = 'دوره با موفقیت حذف شد.';
    $action = 'list';
}

/*  داده برای فرم ویرایش */
$editCourse = null;
if (($action === 'edit') && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$editId]);
    $editCourse = $stmt->fetch();
}

if (!empty($formData) && $action === 'edit') {
    $editCourse = array_merge($editCourse ?: [], [
        'id' => $formData['id'], 'title' => $formData['title'], 'category_id' => $formData['category_id'],
        'instructor' => $formData['instructor'], 'level' => $formData['level'], 'price' => $formData['price'],
        'old_price' => $formData['old_price'], 'hours' => $formData['hours'], 'lessons' => $formData['lessons'],
        'summary' => $formData['summary'], 'description' => $formData['description'],
        'syllabus' => $formData['syllabus'], 'featured' => isset($formData['featured']),
    ]);
} elseif (!empty($formData) && $action === 'create') {
    $editCourse = [
        'id' => 0, 'title' => $formData['title'], 'category_id' => $formData['category_id'],
        'instructor' => $formData['instructor'], 'level' => $formData['level'], 'price' => $formData['price'],
        'old_price' => $formData['old_price'], 'hours' => $formData['hours'], 'lessons' => $formData['lessons'],
        'summary' => $formData['summary'], 'description' => $formData['description'],
        'syllabus' => $formData['syllabus'], 'featured' => isset($formData['featured']),
    ];
}

/*  لیست دوره‌ها (با جستجو) */
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name AS category_name FROM courses c
        JOIN categories cat ON cat.id = c.category_id
        WHERE c.title LIKE ? OR c.instructor LIKE ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT c.*, cat.name AS category_name FROM courses c
        JOIN categories cat ON cat.id = c.category_id
        ORDER BY c.created_at DESC
    ");
}
$courses = $stmt->fetchAll();

$showForm = in_array($action, ['create', 'edit']);

$topbar_action = $showForm
    ? '<a href="courses.php" class="btn btn-outline">← بازگشت به لیست</a>'
    : '<a href="courses.php?action=create" class="btn btn-primary">+ افزودن دوره جدید</a>';

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner <?= $messageType === 'danger' ? 'banner-danger' : 'banner-success' ?>">
  <?= h($message) ?>
</div>
<?php endif; ?>

<?php if ($showForm): ?>
<!--  فرم افزودن / ویرایش دوره  -->
<div class="admin-card">
  <div class="admin-card-head">
    <h3><?= $editCourse && $editCourse['id'] ? 'ویرایش دوره' : 'افزودن دوره جدید' ?></h3>
  </div>
  <form method="post" enctype="multipart/form-data" class="modal-body">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= h($editCourse['id'] ?? 0) ?>">

    <div class="field">
      <label for="fTitle">عنوان دوره</label>
      <input type="text" id="fTitle" name="title" value="<?= h($editCourse['title'] ?? '') ?>" required>
    </div>

    <div class="form-row-2">
      <div class="field">
        <label for="fCategory">دسته‌بندی</label>
        <select id="fCategory" name="category_id" required>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= (isset($editCourse['category_id']) && $editCourse['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="fLevel">سطح</label>
        <select id="fLevel" name="level" required>
          <?php foreach (['مقدماتی','متوسط','پیشرفته'] as $lvl): ?>
          <option value="<?= $lvl ?>" <?= (isset($editCourse['level']) && $editCourse['level'] === $lvl) ? 'selected' : '' ?>><?= $lvl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="fInstructor">نام مدرس</label>
      <input type="text" id="fInstructor" name="instructor" value="<?= h($editCourse['instructor'] ?? '') ?>" required>
    </div>

    <div class="form-row-2">
      <div class="field">
        <label for="fPrice">قیمت (تومان)</label>
        <input type="number" id="fPrice" name="price" min="0" value="<?= h($editCourse['price'] ?? 0) ?>" required>
      </div>
      <div class="field">
        <label for="fOldPrice">قیمت قبل از تخفیف (اختیاری)</label>
        <input type="number" id="fOldPrice" name="old_price" min="0" value="<?= h($editCourse['old_price'] ?? 0) ?>">
      </div>
    </div>

    <div class="form-row-2">
      <div class="field">
        <label for="fHours">مدت دوره (ساعت)</label>
        <input type="number" id="fHours" name="hours" min="0" value="<?= h($editCourse['hours'] ?? 0) ?>" required>
      </div>
      <div class="field">
        <label for="fLessons">تعداد درس‌ها</label>
        <input type="number" id="fLessons" name="lessons" min="0" value="<?= h($editCourse['lessons'] ?? 0) ?>" required>
      </div>
    </div>

    <div class="field">
      <label for="fImage">تصویر دوره</label>
      <input type="file" id="fImage" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
      <?php if (!empty($editCourse['image'])): ?>
        <div class="u-mt-0_6">
          <img src="../<?= h($editCourse['image']) ?>" alt="" class="u-img-preview-sm">
          <span class="field-hint">تصویر فعلی — در صورت انتخاب فایل جدید، جایگزین می‌شود.</span>
        </div>
      <?php else: ?>
        <span class="field-hint">در صورت عدم انتخاب، تصویر پیش‌فرض استفاده می‌شود.</span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="fSummary">توضیح کوتاه</label>
      <textarea id="fSummary" name="summary" class="u-textarea-70" required><?= h($editCourse['summary'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label for="fDescription">توضیحات کامل</label>
      <textarea id="fDescription" name="description" required><?= h($editCourse['description'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label for="fSyllabus">سرفصل‌ها (هر خط یک سرفصل)</label>
      <textarea id="fSyllabus" name="syllabus" placeholder="مثال:&#10;مقدمات HTML&#10;استایل‌دهی با CSS"><?= h($editCourse['syllabus'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label class="filter-opt u-p-0">
        <input type="checkbox" name="featured" class="u-checkbox-18" <?= !empty($editCourse['featured']) ? 'checked' : '' ?>>
        نمایش در دوره‌های ویژه صفحه اصلی
      </label>
    </div>

    <div class="modal-footer u-modal-footer-flat">
      <a href="courses.php" class="btn btn-outline">انصراف</a>
      <button type="submit" class="btn btn-primary">ذخیره دوره</button>
    </div>
  </form>
</div>

<?php else: ?>
<!--  لیست دوره‌ها (Show) -->
<div class="admin-card">
  <div class="admin-card-head">
    <h3>لیست دوره‌ها (<?= count($courses) ?>)</h3>
    <form method="get">
      <input type="text" class="table-search" name="q" value="<?= h($search) ?>" placeholder="جستجو در دوره‌ها...">
    </form>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>دوره</th><th>دسته‌بندی</th><th>سطح</th><th>وضعیت</th><th>قیمت</th><th>دانشجویان</th><th>امتیاز</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($courses)): ?>
        <tr class="empty-row"><td colspan="8">دوره‌ای یافت نشد.</td></tr>
        <?php else: foreach ($courses as $c):
          $statusLabel = ['pending' => 'در انتظار تایید', 'approved' => 'تاییدشده', 'rejected' => 'رد‌شده'][$c['status']];
        ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($c['image']) ?>" alt="">
              <span class="title"><?= h($c['title']) ?></span>
            </div>
          </td>
          <td><?= h($c['category_name']) ?></td>
          <td><span class="badge-level"><?= h($c['level']) ?></span></td>
          <td><span class="status-badge status-<?= $c['status'] ?>"><?= $statusLabel ?></span></td>
          <td><?= fmt_price($c['price']) ?></td>
          <td><?= number_format($c['students']) ?></td>
          <td>★ <?= h($c['rating']) ?></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="ویرایش" href="courses.php?action=edit&edit=<?= $c['id'] ?>">✏️</a>
              <a class="icon-btn danger" title="حذف" href="#" onclick="confirmDelete('courses.php?action=delete&id=<?= $c['id'] ?>'); return false;">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


<div class="modal-overlay" id="deleteModal">
  <div class="modal-box u-modal-box-sm">
    <div class="modal-body confirm-box">
      <div class="ic">🗑️</div>
      <h3>حذف دوره</h3>
      <p>آیا از حذف این دوره مطمئن هستید؟ این عملیات قابل بازگشت نیست.</p>
    </div>
    <div class="modal-footer u-modal-footer-center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('open')">انصراف</button>
      <a class="btn btn-danger" id="confirmDeleteLink" href="#">حذف دوره</a>
    </div>
  </div>
</div>

<script>
  function confirmDelete(url) {
    document.getElementById('confirmDeleteLink').setAttribute('href', url);
    document.getElementById('deleteModal').classList.add('open');
  }
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes_footer.php'; ?>
