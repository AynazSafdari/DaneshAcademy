<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">آکادمی دانش</div>
        <p>دفترچه یادگیری آنلاین شما؛ مسیری ساده برای رسیدن به مهارت‌های جدید.</p>
        <div class="social-row u-mt-1">
          <a href="#" class="u-social-light" aria-label="اینستاگرام"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="u-social-light" aria-label="تلگرام"><i class="fa-brands fa-telegram"></i></a>
          <a href="#" class="u-social-light" aria-label="لینکدین"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>
      <div>
        <h4>دسترسی سریع</h4>
        <a href="<?= $base_url ?? '' ?>courses.php">دوره‌ها</a>
        <a href="<?= $base_url ?? '' ?>blog.php">مقالات</a>
        <a href="<?= $base_url ?? '' ?>about.php">درباره ما</a>
        <a href="<?= $base_url ?? '' ?>contact.php">تماس با ما</a>
      </div>
      <div>
        <h4>حساب کاربری</h4>
        <a href="<?= $base_url ?? '' ?>login.php">ورود</a>
        <a href="<?= $base_url ?? '' ?>signup.php">ثبت‌نام</a>
        <a href="<?= $base_url ?? '' ?>admin/login.php">ورود مدیر</a>
      </div>
      <div>
        <h4>تماس با ما</h4>
        <p>تهران، خیابان آزادی</p>
        <p>info@danesh.ir</p>
        <p>۰۲۱-۱۲۳۴۵۶۷۸</p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© ۱۴۰۵ آکادمی دانش. تمامی حقوق محفوظ است.</span>
    </div>
  </div>
</footer>

<script src="<?= $asset_prefix ?? '' ?>assets/js/main.js"></script>
<?php if (!empty($extra_js)): foreach ($extra_js as $js): ?>
<script src="<?= $asset_prefix ?? '' ?>assets/js/<?= $js ?>.js"></script>
<?php endforeach; endif; ?>
</body>
</html>
