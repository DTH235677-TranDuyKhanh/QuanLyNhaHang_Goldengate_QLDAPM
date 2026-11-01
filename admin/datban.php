<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Quản lý đặt bàn</h3>
    <p>Xác nhận thủ công các yêu cầu và cập nhật trạng thái phục vụ.</p>
  </div>
</div>
<form method="get" action="index.php" class="row g-2 align-items-end mb-3">
  <input type="hidden" name="action" value="datban">
  <?php if ($isAdmin): ?>
    <div class="col-sm-5 col-lg-3">
      <label class="form-label" for="bookingBranchFilter">Chi nhánh</label>
      <select class="form-select" id="bookingBranchFilter" name="cn">
        <option value="0">Tất cả chi nhánh</option>
        <?php foreach ($cns as $branch): ?><option value="<?= (int)$branch['id'] ?>" <?= $cn === (int)$branch['id'] ? 'selected' : '' ?>><?= e($branch['tenchinhanh']) ?></option><?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>
  <div class="col-sm-5 col-lg-3">
    <label class="form-label" for="bookingStatusFilter">Trạng thái</label>
    <select class="form-select" id="bookingStatusFilter" name="tt">
      <option value="">Tất cả trạng thái</option>
      <?php foreach ([
          DatBan::CHO_XAC_NHAN, DatBan::DA_XAC_NHAN, DatBan::DA_DEN,
          DatBan::DA_HUY, DatBan::KHONG_DEN, DatBan::TU_CHOI,
      ] as $status): [$label] = DatBan::nhanTrangThai($status); ?>
        <option value="<?= $status ?>" <?= (string)$tt === (string)$status ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Lọc</button></div>
</form>
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Ngày giờ</th><th>Khách hàng</th><th>Chi nhánh</th><th>Số khách</th><th>Ghi chú</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
        <tbody>
        <?php foreach ($ds as $booking): ?>
          <?php
            [$label, $color] = DatBan::nhanTrangThai($booking['trangthai']);
            $nextStates = (int)$booking['trangthai'] === DatBan::CHO_XAC_NHAN
                ? [DatBan::DA_XAC_NHAN, DatBan::TU_CHOI, DatBan::DA_HUY]
                : ((int)$booking['trangthai'] === DatBan::DA_XAC_NHAN
                    ? [DatBan::DA_DEN, DatBan::KHONG_DEN, DatBan::DA_HUY] : []);
          ?>
          <tr>
            <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($booking['thoi_gian'])) ?></td>
            <td><strong><?= e($booking['hoten']) ?></strong><br><a href="tel:<?= e($booking['sodienthoai']) ?>"><?= e($booking['sodienthoai']) ?></a></td>
            <td><?= e($booking['tenchinhanh']) ?></td>
            <td><?= (int)$booking['so_khach'] ?></td>
            <td><?= e($booking['ghichu'] ?: '—') ?></td>
            <td><span class="badge text-bg-<?= e($color) ?>"><?= e($label) ?></span></td>
            <td>
              <?php if ($nextStates): ?>
                <form method="post" action="index.php" class="d-flex gap-2">
                  <?= csrf_field() ?><input type="hidden" name="action" value="capnhatdatban"><input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                  <select class="form-select form-select-sm" name="trangthai" aria-label="Trạng thái mới">
                    <?php foreach ($nextStates as $status): [$nextLabel] = DatBan::nhanTrangThai($status); ?>
                      <option value="<?= $status ?>"><?= e($nextLabel) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-outline-primary text-nowrap">Lưu</button>
                </form>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$ds): ?><tr><td colspan="7" class="report-empty">Không có yêu cầu đặt bàn phù hợp.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
