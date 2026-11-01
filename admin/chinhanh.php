<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Quản lý chi nhánh</h3>
    <p>Thông tin liên hệ và giờ hoạt động hiển thị cho khách hàng.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xxl-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Danh sách chi nhánh <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Chi nhánh</th><th>Địa chỉ</th><th>Điện thoại</th><th>Giờ hoạt động</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ds as $c): ?>
                <tr>
                  <td class="fw-semibold"><?= e($c['tenchinhanh']) ?></td>
                  <td><?= e($c['diachi']) ?></td>
                  <td><?= e($c['sodienthoai'] ?: '—') ?></td>
                  <td class="text-nowrap"><?= substr($c['giomocua'], 0, 5) ?> – <?= substr($c['giodongcua'], 0, 5) ?></td>
                  <td><span class="badge text-bg-<?= $c['trangthai'] ? 'success' : 'secondary' ?>"><?= $c['trangthai'] ? 'Đang hoạt động' : 'Tạm đóng' ?></span></td>
                  <td>
                    <div class="admin-table-actions">
                      <a class="btn btn-sm btn-outline-primary" href="index.php?action=chinhanh&edit=<?= (int)$c['id'] ?>#branch-form">Sửa</a>
                      <form method="post" action="index.php" onsubmit="return confirm('<?= $c['trangthai'] ? 'Tạm đóng' : 'Mở lại' ?> chi nhánh này?')">
                        <?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaicn"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button class="btn btn-sm btn-outline-secondary"><?= $c['trangthai'] ? 'Tạm đóng' : 'Mở lại' ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$ds): ?><tr><td colspan="6" class="report-empty">Chưa có chi nhánh nào.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xxl-4" id="branch-form">
    <form method="post" action="index.php" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luuchinhanh"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="admin-form-card-header">
        <h4><?= $edit ? 'Cập nhật chi nhánh' : 'Thêm chi nhánh' ?></h4>
        <p>Điền thông tin để khách nhận biết địa điểm.</p>
      </div>
      <div class="admin-form-card-body">
        <div class="mb-3">
          <label class="form-label" for="branchName">Tên chi nhánh <span class="admin-required">*</span></label>
          <input id="branchName" class="form-control" name="ten" maxlength="100" required value="<?= e($edit['tenchinhanh'] ?? '') ?>" placeholder="Ví dụ: GoldenGate Quận 1">
        </div>
        <div class="mb-3">
          <label class="form-label" for="branchAddress">Địa chỉ <span class="admin-required">*</span></label>
          <textarea id="branchAddress" class="form-control" name="diachi" rows="3" maxlength="255" required placeholder="Số nhà, đường, phường, quận, thành phố"><?= e($edit['diachi'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label" for="branchPhone">Số điện thoại</label>
          <input id="branchPhone" type="tel" class="form-control" name="sdt" maxlength="15" autocomplete="tel" value="<?= e($edit['sodienthoai'] ?? '') ?>" placeholder="028 1234 5678">
        </div>
        <div class="admin-form-section">
          <h5 class="admin-form-section-title">Giờ phục vụ</h5>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" for="branchOpen">Mở cửa</label>
              <input id="branchOpen" type="time" class="form-control" name="mo" required value="<?= e(substr($edit['giomocua'] ?? '10:00', 0, 5)) ?>">
            </div>
            <div class="col-6">
              <label class="form-label" for="branchClose">Đóng cửa</label>
              <input id="branchClose" type="time" class="form-control" name="dong" required value="<?= e(substr($edit['giodongcua'] ?? '22:00', 0, 5)) ?>">
            </div>
          </div>
          <div class="form-text mt-2">Để đổi trạng thái mở/tạm đóng, dùng nút trong danh sách.</div>
        </div>
        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action"><?= $edit ? 'Lưu thay đổi' : 'Tạo chi nhánh' ?></button>
          <?php if ($edit): ?><a class="btn btn-outline-secondary" href="index.php?action=chinhanh">Hủy sửa</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
