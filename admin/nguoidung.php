<?php include 'inc/top.php'; $loaiLb = [1 => 'Quản trị tổng', 2 => 'Quản lý chi nhánh', 3 => 'Khách hàng']; ?>
<div class="admin-page-heading">
  <div>
    <h3>Người dùng & nhân sự</h3>
    <p>Tạo tài khoản nhân viên và quản lý quyền truy cập hệ thống.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xxl-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Tài khoản đã đăng ký <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Nhân sự</th><th>Thông tin liên hệ</th><th>Vai trò</th><th>Chi nhánh</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ds as $u): ?>
                <tr>
                  <td class="fw-semibold"><?= e($u['hoten']) ?></td>
                  <td><?= e($u['sodienthoai']) ?><br><small class="text-muted"><?= e($u['email'] ?: 'Chưa có email') ?></small></td>
                  <td><?= e($loaiLb[$u['loai']] ?? 'Không xác định') ?></td>
                  <td><?= e($u['tenchinhanh'] ?? 'Toàn hệ thống') ?></td>
                  <td><span class="badge text-bg-<?= $u['trangthai'] ? 'success' : 'danger' ?>"><?= $u['trangthai'] ? 'Hoạt động' : 'Đã khóa' ?></span></td>
                  <td>
                    <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                      <form method="post" action="index.php" onsubmit="return confirm('<?= $u['trangthai'] ? 'Khóa' : 'Mở khóa' ?> tài khoản này?')">
                        <?= csrf_field() ?><input type="hidden" name="action" value="khoanguoidung"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button class="btn btn-sm btn-outline-<?= $u['trangthai'] ? 'danger' : 'success' ?>"><?= $u['trangthai'] ? 'Khóa' : 'Mở khóa' ?></button>
                      </form>
                    <?php else: ?><span class="small text-muted">Tài khoản của bạn</span><?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$ds): ?><tr><td colspan="6" class="report-empty">Chưa có tài khoản nào.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xxl-4">
    <form method="post" action="index.php" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luunhansu">
      <div class="admin-form-card-header">
        <h4>Tạo tài khoản nhân sự</h4>
        <p>Khách hàng không được tạo từ màn hình này.</p>
      </div>
      <div class="admin-form-card-body">
        <div class="mb-3">
          <label class="form-label" for="staffName">Họ và tên <span class="admin-required">*</span></label>
          <input id="staffName" class="form-control" name="hoten" maxlength="100" autocomplete="name" required placeholder="Tên nhân viên">
        </div>
        <div class="mb-3">
          <label class="form-label" for="staffPhone">Số điện thoại <span class="admin-required">*</span></label>
          <input id="staffPhone" type="tel" class="form-control" name="sdt" inputmode="numeric" autocomplete="tel"
                 pattern="0[0-9]{9}" maxlength="10" required placeholder="0912345678">
          <div class="form-text">Nhập 10 chữ số, bắt đầu bằng số 0.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="staffEmail">Email</label>
          <input id="staffEmail" type="email" class="form-control" name="email" maxlength="100" autocomplete="email" placeholder="nhanvien@example.com">
        </div>
        <div class="mb-3">
          <label class="form-label" for="staffPassword">Mật khẩu tạm thời <span class="admin-required">*</span></label>
          <input id="staffPassword" type="password" class="form-control" name="matkhau" minlength="6" autocomplete="new-password" required>
          <div class="form-text">Tối thiểu 6 ký tự. Nhân viên nên đổi mật khẩu sau khi đăng nhập.</div>
        </div>
        <div class="admin-form-section">
          <h5 class="admin-form-section-title">Phân quyền</h5>
          <div class="mb-3">
            <label class="form-label" for="staffRole">Vai trò <span class="admin-required">*</span></label>
            <select id="staffRole" class="form-select" name="loai" required>
              <option value="2" selected>Quản lý chi nhánh</option>
              <option value="1">Quản trị tổng</option>
            </select>
          </div>
          <div id="staffBranchField" class="mb-2">
            <label class="form-label" for="staffBranch">Chi nhánh phụ trách <span class="admin-required">*</span></label>
            <select id="staffBranch" class="form-select" name="chinhanh">
              <option value="">-- Chọn chi nhánh --</option>
              <?php foreach ($cns as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['tenchinhanh']) ?></option><?php endforeach; ?>
            </select>
            <div class="form-text">Quản lý chỉ xem và xử lý dữ liệu của chi nhánh được giao.</div>
          </div>
        </div>
        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action">Tạo tài khoản</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  const staffRole = document.getElementById('staffRole');
  const staffBranch = document.getElementById('staffBranch');
  const staffBranchField = document.getElementById('staffBranchField');

  function updateStaffBranchRequirement() {
    const manager = staffRole.value === '2';
    staffBranch.required = manager;
    staffBranch.disabled = !manager;
    staffBranchField.hidden = !manager;
  }

  staffRole.addEventListener('change', updateStaffBranchRequirement);
  updateStaffBranchRequirement();
</script>
<?php include 'inc/bottom.php'; ?>
