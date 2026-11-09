<?php
include 'inc/top.php';
$edit = $edit ?: [];
$startValue = !empty($edit['bat_dau']) ? date('Y-m-d\TH:i', strtotime($edit['bat_dau'])) : date('Y-m-d\TH:i');
$endValue = !empty($edit['ket_thuc']) ? date('Y-m-d\TH:i', strtotime($edit['ket_thuc'])) : date('Y-m-d\TH:i', strtotime('+30 days'));
?>
<div class="admin-page-heading">
  <div>
    <h3>Quản lý sự kiện và ưu đãi</h3>
    <p>Đăng thông tin chương trình công khai trên website theo thời gian và chi nhánh.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xxl-8">
    <div class="card"><div class="card-body">
      <h5 class="mb-3">Danh sách sự kiện <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Sự kiện</th><th>Thời gian</th><th>Phạm vi</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($ds as $event): ?>
            <tr>
              <td><strong><?= e($event['tieu_de']) ?></strong>
                <?php if ($event['ma_uu_dai_hienthi']): ?><br><small class="text-muted">Mã: <?= e($event['ma_uu_dai_hienthi']) ?></small><?php endif; ?>
              </td>
              <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($event['bat_dau'])) ?><br>đến <?= date('d/m/Y H:i', strtotime($event['ket_thuc'])) ?></td>
              <td><?= e($event['tenchinhanh'] ?: 'Toàn hệ thống') ?></td>
              <td><span class="badge text-bg-<?= $event['trangthai'] ? 'success' : 'secondary' ?>"><?= $event['trangthai'] ? 'Đang bật' : 'Đã tắt' ?></span></td>
              <td><div class="admin-table-actions justify-content-end">
                <a class="btn btn-sm btn-outline-primary" href="index.php?action=sukien&edit=<?= (int)$event['id'] ?>#event-form">Sửa</a>
                <form method="post" action="index.php">
                  <?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaisukien"><input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
                  <button class="btn btn-sm btn-outline-secondary"><?= $event['trangthai'] ? 'Tắt' : 'Bật' ?></button>
                </form>
              </div></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$ds): ?><tr><td colspan="5" class="report-empty">Chưa có sự kiện.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
  <div class="col-12 col-xxl-4" id="event-form">
    <form method="post" action="index.php" enctype="multipart/form-data" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luusukien"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="admin-form-card-header"><h4><?= $edit ? 'Cập nhật sự kiện' : 'Tạo sự kiện' ?></h4><p>Chỉ sự kiện đang bật và trong thời hạn mới hiển thị công khai.</p></div>
      <div class="admin-form-card-body">
        <div class="mb-3"><label class="form-label" for="eventTitle">Tiêu đề *</label>
          <input id="eventTitle" class="form-control" name="tieu_de" maxlength="255" required value="<?= e($edit['tieu_de'] ?? '') ?>">
        </div>
        <div class="mb-3"><label class="form-label" for="eventDescription">Nội dung *</label>
          <textarea id="eventDescription" class="form-control" name="mo_ta" rows="5" required><?= e($edit['mo_ta'] ?? '') ?></textarea>
        </div>
        <div class="mb-3"><label class="form-label" for="eventImage">Ảnh sự kiện <?= $edit ? '' : '*' ?></label>
          <input id="eventImage" class="form-control" type="file" name="hinh_anh" accept="image/jpeg,image/png,image/webp" <?= $edit ? '' : 'required' ?>>
          <div class="form-text">JPG, PNG hoặc WEBP; tối đa 2 MB. Để trống khi sửa nếu giữ ảnh hiện tại.</div>
        </div>
        <div class="mb-3"><label class="form-label" for="eventOffer">Voucher liên kết (không bắt buộc)</label>
          <select id="eventOffer" class="form-select" name="voucher_id">
            <option value="0">Không gắn voucher</option>
            <?php foreach ($vouchers as $voucher): ?>
              <option value="<?= (int)$voucher['id'] ?>"
                <?= (int)($edit['voucher_id'] ?? 0) === (int)$voucher['id'] ? 'selected' : '' ?>
                <?= !(int)$voucher['trangthai'] ? 'disabled' : '' ?>>
                <?= e($voucher['ma']) ?> · <?= (int)$voucher['trangthai'] ? 'Đang bật' : 'Đã tắt' ?>
                (<?= date('d/m/Y', strtotime($voucher['bat_dau'])) ?>–<?= date('d/m/Y', strtotime($voucher['ket_thuc'])) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Mã hiển thị trên sự kiện được lấy trực tiếp từ voucher. Thời hạn và chi nhánh sự kiện phải khớp điều kiện voucher.</div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6"><label class="form-label" for="eventStart">Bắt đầu *</label><input id="eventStart" class="form-control" type="datetime-local" name="bat_dau" required value="<?= e($startValue) ?>"></div>
          <div class="col-6"><label class="form-label" for="eventEnd">Kết thúc *</label><input id="eventEnd" class="form-control" type="datetime-local" name="ket_thuc" required value="<?= e($endValue) ?>"></div>
        </div>
        <div class="mb-3"><label class="form-label" for="eventBranch">Chi nhánh áp dụng</label>
          <select id="eventBranch" class="form-select" name="chinhanh_id">
            <option value="0">Toàn hệ thống</option>
            <?php foreach ($cns as $branch): ?><option value="<?= (int)$branch['id'] ?>" <?= (int)($edit['chinhanh_id'] ?? 0) === (int)$branch['id'] ? 'selected' : '' ?>><?= e($branch['tenchinhanh']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="trangthai" id="eventEnabled" value="1" <?= !$edit || !empty($edit['trangthai']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="eventEnabled">Hiển thị khi sự kiện còn thời hạn</label>
        </div>
        <div class="admin-form-actions"><button class="btn btn-gg btn-primary-action"><?= $edit ? 'Lưu thay đổi' : 'Tạo sự kiện' ?></button>
          <?php if ($edit): ?><a class="btn btn-outline-secondary" href="index.php?action=sukien">Hủy sửa</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
