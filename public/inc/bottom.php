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
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutConfirmLabel">Xác nhận đăng xuất</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Hủy"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn đăng xuất khỏi tài khoản này không?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a id="logoutConfirmButton" href="index.php?action=dangxuat" class="btn btn-danger">Đăng xuất</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const logoutModal = document.getElementById('logoutConfirmModal');
  if (logoutModal) {
    logoutModal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      const url = trigger && trigger.dataset.logoutUrl;
      const confirmButton = document.getElementById('logoutConfirmButton');
      if (url && confirmButton) confirmButton.href = url;
    });
  }

  document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('form[data-add-to-cart]')) return;

    event.preventDefault();
    const button = form.querySelector('button[type="submit"], button:not([type])');
    if (button && button.disabled) return;
    if (button) button.disabled = true;

    const notify = (type, message) => {
      const container = document.getElementById('cartNotifications');
      while (container.firstChild) container.removeChild(container.firstChild);

      const alert = document.createElement('div');
      alert.className = `alert alert-${type} alert-dismissible fade show shadow`;
      alert.setAttribute('role', type === 'danger' ? 'alert' : 'status');
      alert.append(document.createTextNode(message));

      const close = document.createElement('button');
      close.type = 'button';
      close.className = 'btn-close';
      close.setAttribute('data-bs-dismiss', 'alert');
      close.setAttribute('aria-label', 'Đóng');
      alert.append(close);
      container.append(alert);

      window.setTimeout(() => {
        if (alert.isConnected) bootstrap.Alert.getOrCreateInstance(alert).close();
      }, 5000);
    };

    let response;
    try {
      response = await fetch(form.getAttribute('action'), {
        method: 'POST',
        body: new FormData(form),
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
    } catch (error) {
      notify('danger', 'Không thể kết nối để thêm món vào giỏ hàng. Vui lòng thử lại.');
      if (button) button.disabled = false;
      return;
    }

    let result;
    try {
      result = await response.json();
    } catch (error) {
      notify('danger', 'Không thể thêm món vào giỏ hàng. Vui lòng tải lại trang và thử lại.');
      if (button) button.disabled = false;
      return;
    }

    if (!response.ok) {
      notify('danger', result.message || 'Không thể thêm món vào giỏ hàng. Vui lòng thử lại.');
    } else {
      notify(result.type, result.message);
      const cart = document.querySelector('.nav-cart');
      const count = cart && cart.querySelector('.cart-count');
      if (count) {
        count.textContent = result.cart_count;
        cart.setAttribute('aria-label', `Giỏ hàng, ${result.cart_count} món`);
      }
    }
    if (button) button.disabled = false;
  });
</script>
</body>
</html>
