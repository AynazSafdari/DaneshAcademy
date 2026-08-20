<?php
$page_title = 'مقالات من';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_teacher('login.php');

$teacherId = (int) $_SESSION['user']['id'];
$teacherName = $_SESSION['user']['name'];

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$editId = (int) ($_GET['edit'] ?? 0);
$message = null;
$messageType = 'success';

/*  CREATE / UPDATE — همیشه با status='pending' ذخیره می‌شود */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $errors = [];
    if ($title === '') $errors[] = 'عنوان مقاله را وارد کنید.';
    if ($category === '') $errors[] = 'دسته‌بندی را وارد کنید.';
    if ($summary === '') $errors[] = 'خلاصه مقاله را وارد کنید.';
    if ($content === '') $errors[] = 'متن مقاله را وارد کنید.';

    $oldImage = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$id, $teacherId]);
        $existingArticle = $stmt->fetch();
        if (!$existingArticle) {
            $errors[] = 'دسترسی به این مقاله مجاز نیست.';
        } else {
            $oldImage = $existingArticle['image'];
        }
    }

    if (empty($errors)) {
        $newImage = handle_image_upload('image', 'images/articles', $oldImage);
        $imageToSave = $newImage ?? $oldImage ?? 'images/articles/default-article.jpg';

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE articles SET title=?, category=?, author=?, image=?, summary=?, content=?, status='pending' WHERE id=? AND teacher_id=?");
            $stmt->execute([$title, $category, $teacherName, $imageToSave, $summary, $content, $id, $teacherId]);
            $message = 'مقاله ویرایش شد و برای تایید مجدد به ادمین ارسال شد.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO articles (title, category, author, teacher_id, status, image, summary, content) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?)");
            $stmt->execute([$title, $category, $teacherName, $teacherId, $imageToSave, $summary, $content]);
            $message = 'مقاله جدید ثبت شد و برای تایید به ادمین ارسال شد.';
        }
        $action = 'list';
        $editId = 0;
    } else {
        $message = implode(' ', $errors);
        $messageType = 'danger';
        $action = $id > 0 ? 'edit' : 'create';
        $editId = $id;
        $formData = $_POST;
    }
}

/*  DELETE  */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$id, $teacherId]);
    $img = $stmt->fetchColumn();

    if ($img !== false) {
        $pdo->prepare("DELETE FROM articles WHERE id = ? AND teacher_id = ?")->execute([$id, $teacherId]);
        if ($img && strpos($img, 'default-') === false) {
            $fullPath = __DIR__ . '/../' . $img;
            if (file_exists($fullPath)) @unlink($fullPath);
        }
        $message = 'مقاله با موفقیت حذف شد.';
    } else {
        $message = 'دسترسی به این مقاله مجاز نیست.';
        $messageType = 'danger';
    }
    $action = 'list';
}

/*  داده فرم ویرایش */
$editArticle = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$editId, $teacherId]);
    $editArticle = $stmt->fetch();
}
if (!empty($formData)) {
    $editArticle = [
        'id' => $formData['id'] ?? 0, 'title' => $formData['title'], 'category' => $formData['category'],
        'summary' => $formData['summary'], 'content' => $formData['content'],
        'image' => $editArticle['image'] ?? null,
    ];
}

/*  لیست مقالات خود استاد (با جستجو) */
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE teacher_id = ? AND title LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId, "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE teacher_id = ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
}
$articles = $stmt->fetchAll();

$showForm = in_array($action, ['create', 'edit']);

$topbar_action = $showForm
    ? '<a href="articles.php" class="btn btn-outline">← بازگشت به لیست</a>'
    : '<a href="articles.php?action=create" class="btn btn-primary">+ افزودن مقاله جدید</a>';

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner <?= $messageType === 'danger' ? 'banner-danger' : 'banner-success' ?>">
  <?= h($message) ?>
</div>
<?php endif; ?>

<?php if ($showForm): ?>
<!--  فرم افزودن / ویرایش مقاله  -->
<div class="admin-card">
  <div class="admin-card-head">
    <h3><?= $editArticle && $editArticle['id'] ? 'ویرایش مقاله' : 'افزودن مقاله جدید' ?></h3>
  </div>
  <div class="u-list-padding">
    <p class="u-text-sm-soft">پس از ذخیره، این مقاله برای بررسی و تایید نزد مدیر سایت ارسال می‌شود و تا قبل از تایید، در سایت عمومی نمایش داده نمی‌شود.</p>
  </div>
  <form method="post" enctype="multipart/form-data" class="modal-body">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= h($editArticle['id'] ?? 0) ?>">

    <div class="field">
      <label for="fTitle">عنوان مقاله</label>
      <input type="text" id="fTitle" name="title" value="<?= h($editArticle['title'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label for="fCategory">دسته‌بندی</label>
      <input type="text" id="fCategory" name="category" value="<?= h($editArticle['category'] ?? '') ?>" placeholder="مثال: راهنما" required>
    </div>

    <div class="field">
      <label for="fImage">تصویر مقاله</label>
      <input type="file" id="fImage" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
      <?php if (!empty($editArticle['image'])): ?>
        <div class="u-mt-0_6">
          <img src="../<?= h($editArticle['image']) ?>" alt="" class="u-img-preview-sm">
          <span class="field-hint">تصویر فعلی — در صورت انتخاب فایل جدید، جایگزین می‌شود.</span>
        </div>
      <?php else: ?>
        <span class="field-hint">در صورت عدم انتخاب، تصویر پیش‌فرض استفاده می‌شود.</span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="fSummary">خلاصه مقاله</label>
      <textarea id="fSummary" name="summary" class="u-textarea-70" required><?= h($editArticle['summary'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label for="fContent">متن کامل مقاله (هر پاراگراف با خط خالی جدا شود)</label>
      <textarea id="fContent" name="content" class="u-textarea-160" required><?= h($editArticle['content'] ?? '') ?></textarea>
    </div>

    <div class="modal-footer u-modal-footer-flat">
      <a href="articles.php" class="btn btn-outline">انصراف</a>
      <button type="submit" class="btn btn-primary">ذخیره و ارسال برای تایید</button>
    </div>
  </form>
</div>

<?php else: ?>
<!--  لیست مقالات استاد (Show) -->
<div class="admin-card">
  <div class="admin-card-head">
    <h3>مقالات من (<?= count($articles) ?>)</h3>
    <form method="get">
      <input type="text" class="table-search" name="q" value="<?= h($search) ?>" placeholder="جستجو در مقالات من...">
    </form>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>مقاله</th><th>دسته‌بندی</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr class="empty-row"><td colspan="5">هنوز مقاله‌ای ثبت نکرده‌اید.</td></tr>
        <?php else: foreach ($articles as $a):
          $statusLabel = ['pending' => 'در انتظار تایید', 'approved' => 'تاییدشده', 'rejected' => 'رد‌شده'][$a['status']];
        ?>
        <tr>
          <td>
            <div class="row-title-cell">
              <img class="row-thumb" src="../<?= h($a['image']) ?>" alt="">
              <span class="title"><?= h($a['title']) ?></span>
            </div>
          </td>
          <td><span class="tag"><?= h($a['category']) ?></span></td>
          <td><span class="status-badge status-<?= $a['status'] ?>"><?= $statusLabel ?></span></td>
          <td><?= fmt_date($a['created_at']) ?></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="ویرایش" href="articles.php?action=edit&edit=<?= $a['id'] ?>">✏️</a>
              <a class="icon-btn danger" title="حذف" href="#" onclick="confirmDelete('articles.php?action=delete&id=<?= $a['id'] ?>'); return false;">🗑️</a>
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
      <h3>حذف مقاله</h3>
      <p>آیا از حذف این مقاله مطمئن هستید؟ این عملیات قابل بازگشت نیست.</p>
    </div>
    <div class="modal-footer u-modal-footer-center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('open')">انصراف</button>
      <a class="btn btn-danger" id="confirmDeleteLink" href="#">حذف مقاله</a>
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
