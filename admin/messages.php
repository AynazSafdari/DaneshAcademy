<?php
$page_title = 'پیام‌های تماس';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin('login.php');

$message = null;
$action = $_GET['action'] ?? 'list';
$viewId = (int) ($_GET['view'] ?? 0);

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
    $message = 'پیام با موفقیت حذف شد.';
}

$viewMessage = null;
if ($viewId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$viewId]);
    $viewMessage = $stmt->fetch();
    if ($viewMessage && !$viewMessage['is_read']) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$viewId]);
        $viewMessage['is_read'] = 1;
    }
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE name LIKE ? OR subject LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
}
$messages = $stmt->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<?php if ($message): ?>
<div class="admin-banner banner-success">
  <?= h($message) ?>
</div>
<?php endif; ?>

<?php if ($viewMessage): ?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3>جزئیات پیام</h3>
    <a href="messages.php" class="btn btn-outline btn-sm">← بازگشت به لیست</a>
  </div>
  <div class="modal-body">
    <p><strong class="u-color-pine">نام:</strong> <?= h($viewMessage['name']) ?></p>
    <p class="u-mt-0_6"><strong class="u-color-pine">ایمیل:</strong> <?= h($viewMessage['email']) ?></p>
    <p class="u-mt-0_6"><strong class="u-color-pine">موضوع:</strong> <?= h($viewMessage['subject']) ?></p>
    <p class="u-mt-0_6"><strong class="u-color-pine">تاریخ:</strong> <?= fmt_date($viewMessage['created_at']) ?></p>
    <div class="u-message-box">
      <p class="u-color-ink"><?= nl2br(h($viewMessage['message'])) ?></p>
    </div>
    <div class="modal-footer u-modal-footer-flat-start">
      <a class="btn btn-danger" href="#" onclick="confirmDelete('messages.php?action=delete&id=<?= $viewMessage['id'] ?>'); return false;">حذف پیام</a>
    </div>
  </div>
</div>

<?php else: ?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3>پیام‌های دریافتی (<?= count($messages) ?>)</h3>
    <form method="get">
      <input type="text" class="table-search" name="q" value="<?= h($search) ?>" placeholder="جستجو در پیام‌ها...">
    </form>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>فرستنده</th><th>موضوع</th><th>تاریخ</th><th>وضعیت</th><th>عملیات</th></tr>
      </thead>
      <tbody>
        <?php if (empty($messages)): ?>
        <tr class="empty-row"><td colspan="5">پیامی دریافت نشده است.</td></tr>
        <?php else: foreach ($messages as $m): ?>
        <tr>
          <td><strong class="u-color-pine"><?= h($m['name']) ?></strong><br><span class="u-text-sm-soft"><?= h($m['email']) ?></span></td>
          <td><?= h($m['subject']) ?></td>
          <td><?= fmt_date($m['created_at']) ?></td>
          <td><span class="status-pill <?= $m['is_read'] ? 'read' : 'unread' ?>"><?= $m['is_read'] ? 'خوانده‌شده' : 'جدید' ?></span></td>
          <td>
            <div class="row-actions">
              <a class="icon-btn" title="مشاهده" href="messages.php?view=<?= $m['id'] ?>">👁️</a>
              <a class="icon-btn danger" title="حذف" href="#" onclick="confirmDelete('messages.php?action=delete&id=<?= $m['id'] ?>'); return false;">🗑️</a>
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
      <h3>حذف پیام</h3>
      <p>آیا از حذف این پیام مطمئن هستید؟</p>
    </div>
    <div class="modal-footer u-modal-footer-center">
      <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('open')">انصراف</button>
      <a class="btn btn-danger" id="confirmDeleteLink" href="#">حذف پیام</a>
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
