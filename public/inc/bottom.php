</main>

<!-- ═══════════════════════════════════════════════════
     FOOTER - GoldenGate Restaurant Chain
     ═══════════════════════════════════════════════════ -->
<footer class="mt-5">
  <!-- Footer Main Content -->
  <div class="footer-main">
    <div class="container">
      <div class="row g-4">

        <!-- ── Cột 1: Thông tin cửa hàng ── -->
        <div class="col-lg-4 col-md-6 footer-col">
          <a class="footer-brand" href="index.php">
            <span class="brand-mark">G</span>
            <span class="footer-brand-text">
              <span>GOLDEN</span>
              <strong>GATE</strong>
            </span>
          </a>
          <p class="footer-desc">
            Chuỗi nhà hàng ẩm thực Việt hàng đầu — phục vụ những món ăn tinh hoa được chế biến từ nguyên liệu tươi ngon, mang đến trải nghiệm ẩm thực đẳng cấp cho thực khách.
          </p>
          <div class="footer-mission">
            <span class="footer-mission-icon">✦</span>
            <p>"Chúng tôi cam kết mang đến hương vị Việt Nam đích thực, dịch vụ tận tâm và không gian sang trọng trong mỗi bữa ăn."</p>
          </div>
          <?php if (!empty($cb)): ?>
          <div class="footer-contact-item">
            <span class="footer-contact-icon">📍</span>
            <div class="footer-contact-text">
              <small>Chi nhánh hiện tại</small>
              <span><?= e($cb['tenchinhanh']) ?> — <?= e($cb['diachi']) ?></span>
            </div>
          </div>
          <?php endif; ?>
          <div class="footer-social">
            <a href="#" title="Facebook" aria-label="Facebook">📘</a>
            <a href="#" title="Instagram" aria-label="Instagram">📷</a>
            <a href="#" title="YouTube" aria-label="YouTube">▶</a>
            <a href="#" title="TikTok" aria-label="TikTok">♪</a>
          </div>
        </div>

        <!-- ── Cột 2: Liên hệ ── -->
        <div class="col-lg-3 col-md-6 footer-col">
          <h6 class="footer-heading">Liên hệ</h6>

          <div class="footer-contact-item">
            <span class="footer-contact-icon">📞</span>
            <div class="footer-contact-text">
              <small>Hotline đặt món</small>
              <a href="tel:19001234">1900 1234</a>
              <div class="footer-hotline">
                <span>☎ 028 3456 7890</span>
              </div>
            </div>
          </div>

          <div class="footer-contact-item">
            <span class="footer-contact-icon">✉</span>
            <div class="footer-contact-text">
              <small>Email</small>
              <a href="mailto:info@goldengate.vn">info@goldengate.vn</a>
            </div>
          </div>

          <div class="footer-contact-item">
            <span class="footer-contact-icon">🏢</span>
            <div class="footer-contact-text">
              <small>Trụ sở chính</small>
              <span>123 Nguyễn Huệ, Phường Bến Nghé,<br>Quận 1, TP. Hồ Chí Minh</span>
            </div>
          </div>

          <div class="footer-contact-item">
            <span class="footer-contact-icon">🕐</span>
            <div class="footer-contact-text">
              <small>Giờ hoạt động</small>
              <span>
                <?php if (!empty($cb)): ?>
                  <?= substr($cb['giomocua'], 0, 5) ?> — <?= substr($cb['giodongcua'], 0, 5) ?> (Hàng ngày)
                <?php else: ?>
                  10:00 — 22:00 (Hàng ngày)
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>

        <!-- ── Cột 3: Hỗ trợ khách hàng ── -->
        <div class="col-lg-2 col-md-6 footer-col">
          <h6 class="footer-heading">Hỗ trợ</h6>
          <ul class="footer-links">
            <li><a href="index.php"><span class="link-icon">›</span> Hướng dẫn đặt hàng</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Hướng dẫn thanh toán</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Chính sách giao hàng</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Chính sách đổi trả</a></li>
            <li><a href="index.php?action=tracuu"><span class="link-icon">›</span> Tra cứu đơn hàng</a></li>
            <li><a href="index.php?action=datban"><span class="link-icon">›</span> Đặt bàn trực tuyến</a></li>
          </ul>
        </div>

        <!-- ── Cột 4: Chính sách ── -->
        <div class="col-lg-3 col-md-6 footer-col">
          <h6 class="footer-heading">Chính sách</h6>
          <ul class="footer-links">
            <li><a href="index.php"><span class="link-icon">›</span> Chính sách bảo mật</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Điều khoản sử dụng</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Chính sách thanh toán</a></li>
            <li><a href="index.php"><span class="link-icon">›</span> Chính sách hoàn tiền</a></li>
            <li><a href="index.php?action=sukien"><span class="link-icon">›</span> Sự kiện & ưu đãi</a></li>
          </ul>

          <div style="margin-top:1.4rem;">
            <h6 class="footer-heading">Thanh toán</h6>
            <div class="footer-payment-icons">
              <span>💵 COD</span>
              <span>🏦 VietQR</span>
              <span>💳 Banking</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer Bottom Bar -->
  <div class="footer-bottom">
    <div class="container">
      <div class="row align-items-center g-2">
        <div class="col-md-5">
          © <?= date('Y') ?> <strong style="color:#c4b89e;">GoldenGate</strong>. Bảo lưu mọi quyền.
        </div>
        <div class="col-md-4 d-flex justify-content-md-center">
          <div class="footer-bottom-links">
            <a href="index.php">Bảo mật</a>
            <a href="index.php">Điều khoản</a>
            <a href="index.php">Sitemap</a>
          </div>
        </div>
        <div class="col-md-3 d-flex justify-content-md-end">
          <div class="footer-payment">
            <span class="footer-payment-label">Chấp nhận:</span>
            <div class="footer-payment-icons">
              <span>COD</span>
              <span>QR</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
<button type="button" id="backToTop" class="back-to-top" aria-label="Quay lại đầu trang" title="Quay lại đầu trang">
  <svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M12 19V5"></path>
    <path d="M5 12l7-7 7 7"></path>
  </svg>
</button>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  var btn = document.getElementById('backToTop');
  if (!btn) return;
  function toggleBtn() {
    if (window.scrollY > 260) {
      btn.classList.add('show');
    } else {
      btn.classList.remove('show');
    }
  }
  window.addEventListener('scroll', toggleBtn, { passive: true });
  toggleBtn();
  btn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();
</script>
</body>
</html>
