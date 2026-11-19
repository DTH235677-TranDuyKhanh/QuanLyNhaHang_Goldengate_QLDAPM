<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Đơn hàng</h3>
    <p>Tra cứu và cập nhật trạng thái xử lý đơn hàng.</p>
  </div>
</div>
<form method="get" class="admin-inline-filter admin-filter-form mb-3">
  <input type="hidden" name="action" value="donhang">
  <?php if ($isAdmin): ?>
    <div class="filter-field">
      <label class="form-label small fw-semibold" for="orderBranch">Chi nhánh</label>
      <select id="orderBranch" name="cn" class="form-select">
        <option value="">Tất cả chi nhánh</option>
        <?php foreach ($cns as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$cn ? 'selected' : '' ?>><?= e($c['tenchinhanh']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>
  <div class="filter-field">
    <label class="form-label small fw-semibold" for="orderStatus">Trạng thái</label>
    <select id="orderStatus" name="tt" class="form-select">
      <option value="">Mọi trạng thái</option>
      <?php foreach ([0, 1, 2, 3, 4] as $s): ?>
        <option value="<?= $s ?>" <?= (string)$tt === (string)$s ? 'selected' : '' ?>><?= e(trangthai_don($s)[0]) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-gg">Lọc đơn hàng</button>
  <a class="btn btn-outline-secondary" href="index.php?action=donhang">Xóa bộ lọc</a>
</form>
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
      <h5 class="mb-0">Kết quả</h5>
      <span class="badge text-bg-light"><?= count($ds) ?> đơn</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>Mã đơn</th><th>Thời gian</th><th>Chi nhánh</th><th>Khách hàng</th><th>Hình thức</th><th>Thanh toán</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($ds as $d): [$lb, $cl] = trangthai_don($d['trangthai']); ?>
            <tr>
              <td class="fw-semibold text-nowrap"><?= e(maDon($d['id'])) ?></td>
              <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></td>
              <td><?= e($d['tenchinhanh']) ?></td>
              <td><?= e($d['hoten_nhan']) ?><br><small class="text-muted"><?= e($d['sodt_nhan']) ?></small></td>
              <td><?= e(hinhthuc_don($d['hinhthuc'])) ?></td>
              <?php [$paymentLabel, $paymentColor] = trangthai_thanh_toan($d['trangthai_tt']); ?>
              <td><span class="badge text-bg-<?= e($paymentColor) ?>"><?= e($paymentLabel) ?></span></td>
              <td class="text-end text-nowrap fw-semibold"><?= money($d['tongtien']) ?></td>
              <td><span class="badge text-bg-<?= e($cl) ?>"><?= e($lb) ?></span></td>
              <td><a class="btn btn-sm btn-outline-primary" href="index.php?action=chitietdon&id=<?= (int)$d['id'] ?>">Chi tiết</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$ds): ?><tr><td colspan="9" class="report-empty">Không có đơn phù hợp với bộ lọc.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if (count($ds) >= 200): ?><p class="form-text mt-3 mb-0">Đang hiển thị tối đa 200 đơn gần nhất. Hãy lọc theo chi nhánh hoặc trạng thái để thu hẹp kết quả.</p><?php endif; ?>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
