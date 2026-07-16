<?php
$page_title = 'کاربران ثبت‌نام‌شده';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$message = null;
$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$id]);
    $message = 'کاربر با موفقیت حذف شد.';
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student' AND (name LIKE ? OR username LIKE ?) ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner banner-success">
  <?= h($message) ?>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3>لیست کاربران (<?= count($users) ?>)</h3>
    <form method="get">
      <input type="text" class="table-search" name="q" value="<?= h($search) ?>" placeholder="جستجو در کاربران...">
    </form>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>نام</th><th>نام کاربری / ایمیل</th><th>تاریخ ثبت‌نام</th><th>نقش</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr class="empty-row"><td colspan="5">کاربری یافت نشد. کاربران از طریق صفحه ثبت‌نام سایت اضافه می‌شوند.</td></tr>
        <?php else: foreach ($users as $u): ?>
        <tr>
          <td><strong class="u-color-pine"><?= h($u['name']) ?></strong></td>
          <td><?= h($u['username']) ?></td>
          <td><?= fmt_date($u['created_at']) ?></td>
          <td><span class="tag tag-pine">دانشجو</span></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn danger" title="حذف" href="#" onclick="confirmDelete('users.php?action=delete&id=<?= $u['id'] ?>'); return false;">🗑️</a>
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
      <h3>حذف کاربر</h3>
      <p>آیا از حذف این کاربر مطمئن هستید؟</p>
    </div>
    <div class="modal-footer u-modal-footer-center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('open')">انصراف</button>
      <a class="btn btn-danger" id="confirmDeleteLink" href="#">حذف کاربر</a>
    </div>
  </div>
</div>

<script>
  function confirmDelete(url) {
    document.getElementById('confirmDeleteLink').setAttribute('href', url);
    document.getElementById('deleteModal').classList.add('open');
  }
</script>

<?php require __DIR__ . '/includes_footer.php'; ?>
