<?php
$page_title = 'مدیریت دسته‌بندی‌ها';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$editId = (int) ($_GET['edit'] ?? 0);
$message = null;
$messageType = 'success';

/* CREATE / UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '') ?: '📁';

    $errors = [];
    if ($name === '') $errors[] = 'نام دسته‌بندی را وارد کنید.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $check->execute([$name, $id]);
        if ($check->fetch()) {
            $errors[] = 'دسته‌بندی‌ای با این نام از قبل وجود دارد.';
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $id]);
            $message = 'دسته‌بندی با موفقیت ویرایش شد.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
            $stmt->execute([$name, $icon]);
            $message = 'دسته‌بندی جدید با موفقیت اضافه شد.';
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

/* DELETE  */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE category_id = ?");
    $check->execute([$id]);
    $coursesUsingIt = (int) $check->fetchColumn();

    if ($coursesUsingIt > 0) {
        $message = "این دسته‌بندی قابل حذف نیست چون $coursesUsingIt دوره به آن متصل است.";
        $messageType = 'danger';
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $message = 'دسته‌بندی با موفقیت حذف شد.';
    }
    $action = 'list';
}

/*  edit form */
$editCategory = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}
if (!empty($formData)) {
    $editCategory = ['id' => $formData['id'] ?? 0, 'name' => $formData['name'], 'icon' => $formData['icon']];
}

/*  لیست دسته‌بندی‌ها با تعداد دوره هرکدام */
$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM courses WHERE category_id = c.id) AS course_count
    FROM categories c
    ORDER BY c.name
")->fetchAll();

$showForm = in_array($action, ['create', 'edit']);

$topbar_action = $showForm
    ? '<a href="categories.php" class="btn btn-outline">← بازگشت به لیست</a>'
    : '<a href="categories.php?action=create" class="btn btn-primary">+ افزودن دسته‌بندی جدید</a>';

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner <?= $messageType === 'danger' ? 'banner-danger' : 'banner-success' ?>">
  <?= h($message) ?>
</div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="admin-card">
  <div class="admin-card-head">
    <h3><?= $editCategory && $editCategory['id'] ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید' ?></h3>
  </div>
  <form method="post" class="modal-body">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= h($editCategory['id'] ?? 0) ?>">

    <div class="form-row-2">
      <div class="field">
        <label for="fName">نام دسته‌بندی</label>
        <input type="text" id="fName" name="name" value="<?= h($editCategory['name'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label for="fIcon">آیکون (ایموجی)</label>
        <input type="text" id="fIcon" name="icon" value="<?= h($editCategory['icon'] ?? '📁') ?>" placeholder="مثال: 💻">
      </div>
    </div>

    <div class="modal-footer u-modal-footer-flat">
      <a href="categories.php" class="btn btn-outline">انصراف</a>
      <button type="submit" class="btn btn-primary">ذخیره دسته‌بندی</button>
    </div>
  </form>
</div>

<?php else: ?>
<div class="admin-card">
  <div class="admin-card-head">
    <h3>لیست دسته‌بندی‌ها (<?= count($categories) ?>)</h3>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>آیکون</th><th>نام</th><th>تعداد دوره</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($categories)): ?>
        <tr class="empty-row"><td colspan="4">هیچ دسته‌بندی‌ای ثبت نشده است.</td></tr>
        <?php else: foreach ($categories as $cat): ?>
        <tr>
          <td class="u-icon-cell"><?= $cat['icon'] ?></td>
          <td><strong class="u-color-pine"><?= h($cat['name']) ?></strong></td>
          <td><?= (int)$cat['course_count'] ?></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="ویرایش" href="categories.php?action=edit&edit=<?= $cat['id'] ?>">✏️</a>
              <a class="icon-btn danger" title="حذف" href="#" onclick="confirmDelete('categories.php?action=delete&id=<?= $cat['id'] ?>'); return false;">🗑️</a>
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
      <h3>حذف دسته‌بندی</h3>
      <p>آیا از حذف این دسته‌بندی مطمئن هستید؟ (اگر دوره‌ای به آن متصل باشد، حذف امکان‌پذیر نخواهد بود.)</p>
    </div>
    <div class="modal-footer u-modal-footer-center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('open')">انصراف</button>
      <a class="btn btn-danger" id="confirmDeleteLink" href="#">حذف دسته‌بندی</a>
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
