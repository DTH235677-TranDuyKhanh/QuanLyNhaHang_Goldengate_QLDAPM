<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Tồn kho & thực đơn</h3>
    <p>Cập nhật số lượng có thể bán và trạng thái phục vụ theo từng chi nhánh.</p>
  </div>
</div>
<?php if ($isAdmin): ?>
  <form method="get" class="admin-inline-filter admin-filter-form mb-3">
    <input type="hidden" name="action" value="tonkho">
    <div class="filter-field">
      <label class="form-label small fw-semibold" for="inventoryBranch">Chi nhánh đang chỉnh sửa</label>
      <select id="inventoryBranch" name="cn" class="form-select" required>
        <?php foreach ($cns as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$cn ? 'selected' : '' ?>><?= e($c['tenchinhanh']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-outline-primary">Chọn chi nhánh</button>
  </form>
<?php else: ?>
  <div class="alert alert-info py-2">Đang quản lý tồn kho cho: <strong><?= e($cns[0]['tenchinhanh'] ?? '') ?></strong></div>
<?php endif; ?>

<form method="post" action="index.php">
  <?= csrf_field() ?><input type="hidden" name="action" value="luutonkho"><input type="hidden" name="cn" value="<?= (int)$cn ?>">
  <div class="card">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
          <h5 class="mb-1">Danh sách món tại chi nhánh</h5>
          <p class="text-muted small mb-0">Món sắp hết được đánh dấu màu vàng. Bỏ chọn “Đang phục vụ” để ẩn món khỏi thực đơn chi nhánh.</p>
        </div>
        <span class="badge text-bg-light"><?= count($ds) ?> món</span>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Món ăn</th><th>Nhóm</th><th class="text-end">Giá bán</th><th style="width:10rem">Số lượng tồn</th><th class="text-center">Đang phục vụ</th></tr></thead>
          <tbody>
            <?php foreach ($ds as $m): ?>
              <?php $lowStock = (int)$m['soluongton'] <= 5 && $m['conphucvu']; ?>
              <tr class="<?= $lowStock ? 'table-warning' : '' ?>">
                <td class="fw-semibold"><?= e($m['tenmon']) ?><?php if ($lowStock): ?><div class="small text-warning-emphasis fw-normal">Tồn thấp</div><?php endif; ?></td>
                <td><?= e($m['tennhom']) ?></td>
                <td class="text-end text-nowrap"><?= money($m['giaban']) ?></td>
                <td>
                  <label class="visually-hidden" for="stock-<?= (int)$m['id'] ?>">Số lượng tồn của <?= e($m['tenmon']) ?></label>
                  <input id="stock-<?= (int)$m['id'] ?>" type="number" min="0" step="1" required class="form-control form-control-sm"
                         name="ton[<?= (int)$m['id'] ?>]" value="<?= (int)$m['soluongton'] ?>">
                </td>
                <td class="text-center admin-status-cell">
                  <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                    <input id="service-<?= (int)$m['id'] ?>" type="checkbox" class="form-check-input m-0" role="switch"
                           name="phucvu[<?= (int)$m['id'] ?>]" <?= $m['conphucvu'] ? 'checked' : '' ?>>
                    <label class="form-check-label small" id="service-label-<?= (int)$m['id'] ?>" for="service-<?= (int)$m['id'] ?>"><?= $m['conphucvu'] ? 'Phục vụ' : 'Tạm ngưng' ?></label>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$ds): ?><tr><td colspan="5" class="report-empty">Chưa có món nào để quản lý tại chi nhánh này.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($ds): ?>
        <div class="admin-form-actions justify-content-end">
          <button class="btn btn-gg btn-primary-action">Lưu thay đổi tồn kho</button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</form>
<script>
  document.querySelectorAll('[id^="service-"]:not([id^="service-label-"])').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      const label = document.getElementById('service-label-' + this.id.slice('service-'.length));
      if (label) label.textContent = this.checked ? 'Phục vụ' : 'Tạm ngưng';
    });
  });
</script>
<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Lịch sử biến động tồn kho</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>Thời gian</th><th>Món</th><th>Loại</th><th class="text-end">Biến động</th><th>Tham chiếu / ghi chú</th><th>Người cập nhật</th></tr></thead>
        <tbody>
        <?php foreach ($history as $entry): ?>
          <tr>
            <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($entry['ngaytao'])) ?></td>
            <td><?= e($entry['tenmon']) ?></td>
            <td><?= e(['nhap_hang' => 'Nhập hàng', 'dieu_chinh' => 'Điều chỉnh', 'ban_hang' => 'Bán hàng', 'hoan_hang' => 'Hoàn kho'][$entry['loai']] ?? $entry['loai']) ?></td>
            <td class="text-end <?= (int)$entry['bien_dong'] < 0 ? 'text-danger' : 'text-success' ?>"><?= (int)$entry['bien_dong'] > 0 ? '+' : '' ?><?= (int)$entry['bien_dong'] ?></td>
            <td><?= e(trim(($entry['tham_chieu'] ?? '') . ' ' . ($entry['ghi_chu'] ?? ''))) ?></td>
            <td><?= e($entry['hoten'] ?? 'Hệ thống') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$history): ?><tr><td colspan="6" class="text-muted text-center py-3">Chưa có biến động tồn kho được ghi nhận.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
