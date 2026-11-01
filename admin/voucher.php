<?php
include 'inc/top.php';
$edit = $edit ?: [];
$startValue = !empty($edit['bat_dau']) ? date('Y-m-d\TH:i', strtotime($edit['bat_dau'])) : date('Y-m-d\TH:i');
$endValue = !empty($edit['ket_thuc']) ? date('Y-m-d\TH:i', strtotime($edit['ket_thuc'])) : date('Y-m-d\TH:i', strtotime('+30 days'));
?>
<div class="admin-page-heading">
  <div>
    <h3>Quản lý voucher</h3>
    <p>Tạo mã giảm giá và giới hạn thời gian, giá trị đơn, lượt dùng hoặc chi nhánh.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xxl-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Danh sách voucher <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Mã</th><th>Ưu đãi</th><th>Thời hạn</th><th>Lượt dùng</th><th>Phạm vi</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($ds as $voucher): ?>
              <?php
                $offer = $voucher['loai'] === 'phantram'
                    ? rtrim(rtrim(number_format((float)$voucher['giatri'], 2, '.', ''), '0'), '.') . '%'
                    : money($voucher['giatri']);
              ?>
              <tr>
                <td><strong><?= e($voucher['ma']) ?></strong><br><small class="text-muted">Đơn từ <?= money($voucher['don_toi_thieu']) ?></small></td>
                <td><?= e($offer) ?><?php if ($voucher['giam_toi_da'] !== null): ?><br><small class="text-muted">Tối đa <?= money($voucher['giam_toi_da']) ?></small><?php endif; ?></td>
                <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($voucher['bat_dau'])) ?><br>đến <?= date('d/m/Y H:i', strtotime($voucher['ket_thuc'])) ?></td>
                <td><?= (int)$voucher['da_dung'] ?> / <?= $voucher['gioi_han'] === null ? '∞' : (int)$voucher['gioi_han'] ?></td>
                <td><?= e($voucher['tenchinhanh'] ?: 'Toàn hệ thống') ?></td>
                <td><span class="badge text-bg-<?= $voucher['trangthai'] ? 'success' : 'secondary' ?>"><?= $voucher['trangthai'] ? 'Đang bật' : 'Đã tắt' ?></span></td>
                <td>
                  <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary" href="index.php?action=voucher&edit=<?= (int)$voucher['id'] ?>#voucher-form">Sửa</a>
                    <form method="post" action="index.php">
                      <?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaivoucher"><input type="hidden" name="id" value="<?= (int)$voucher['id'] ?>">
                      <button class="btn btn-sm btn-outline-secondary"><?= $voucher['trangthai'] ? 'Tắt' : 'Bật' ?></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$ds): ?><tr><td colspan="7" class="report-empty">Chưa có voucher.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xxl-4" id="voucher-form">
    <form method="post" action="index.php" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luuvoucher"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="admin-form-card-header">
        <h4><?= $edit ? 'Cập nhật voucher' : 'Tạo voucher' ?></h4>
        <p>Mã nhập ở trang thanh toán, điều kiện sẽ được kiểm tra lại khi tạo đơn.</p>
      </div>
      <div class="admin-form-card-body">
        <div class="mb-3">
          <label class="form-label" for="voucherCode">Mã voucher *</label>
          <input id="voucherCode" class="form-control text-uppercase" name="ma" maxlength="40" pattern="[A-Za-z0-9_-]{3,40}" required value="<?= e($edit['ma'] ?? '') ?>" placeholder="Ví dụ: CHAOMUNG10">
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label" for="voucherType">Kiểu giảm *</label>
            <select id="voucherType" class="form-select" name="loai" required>
              <option value="phantram" <?= ($edit['loai'] ?? '') === 'phantram' ? 'selected' : '' ?>>Phần trăm</option>
              <option value="tienmat" <?= ($edit['loai'] ?? '') === 'tienmat' ? 'selected' : '' ?>>Số tiền</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label" for="voucherValue">Giá trị *</label>
            <input id="voucherValue" class="form-control" type="number" name="giatri" min="1" step="0.01" required value="<?= e($edit['giatri'] ?? '') ?>">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label" for="voucherMin">Đơn tối thiểu</label>
            <input id="voucherMin" class="form-control" type="number" name="don_toi_thieu" min="0" step="1000" value="<?= e($edit['don_toi_thieu'] ?? '0') ?>">
          </div>
          <div class="col-6">
            <label class="form-label" for="voucherMax">Giảm tối đa</label>
            <input id="voucherMax" class="form-control" type="number" name="giam_toi_da" min="1" step="1000" value="<?= e($edit['giam_toi_da'] ?? '') ?>" placeholder="Không giới hạn">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label" for="voucherStart">Bắt đầu *</label>
            <input id="voucherStart" class="form-control" type="datetime-local" name="bat_dau" required value="<?= e($startValue) ?>">
          </div>
          <div class="col-6">
            <label class="form-label" for="voucherEnd">Kết thúc *</label>
            <input id="voucherEnd" class="form-control" type="datetime-local" name="ket_thuc" required value="<?= e($endValue) ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="voucherLimit">Giới hạn lượt dùng</label>
          <input id="voucherLimit" class="form-control" type="number" name="gioi_han" min="1" step="1" value="<?= e($edit['gioi_han'] ?? '') ?>" placeholder="Để trống nếu không giới hạn">
          <?php if ($edit): ?><div class="form-text">Đã sử dụng: <?= (int)$edit['da_dung'] ?> lượt.</div><?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="voucherBranch">Chi nhánh áp dụng</label>
          <select id="voucherBranch" class="form-select" name="chinhanh_id">
            <option value="0">Toàn hệ thống</option>
            <?php foreach ($cns as $branch): ?><option value="<?= (int)$branch['id'] ?>" <?= (int)($edit['chinhanh_id'] ?? 0) === (int)$branch['id'] ? 'selected' : '' ?>><?= e($branch['tenchinhanh']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="trangthai" id="voucherEnabled" value="1" <?= !$edit || !empty($edit['trangthai']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="voucherEnabled">Cho phép khách sử dụng</label>
        </div>
        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action"><?= $edit ? 'Lưu thay đổi' : 'Tạo voucher' ?></button>
          <?php if ($edit): ?><a class="btn btn-outline-secondary" href="index.php?action=voucher">Hủy sửa</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
