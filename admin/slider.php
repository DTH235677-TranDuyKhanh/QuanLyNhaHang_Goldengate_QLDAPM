<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Banner trang chủ</h3>
    <p>Quản lý nội dung hình ảnh và lời kêu gọi hành động trên trang chủ.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xxl-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Danh sách banner <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Banner</th><th>Vị trí</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ds as $s): ?>
                <?php $img = $s['hinh_anh'] ?? ''; if ($img && !preg_match('/^https?:\/\//i', $img)) $img = img_url_dir($img, 'slider'); ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <?php if ($img): ?><img src="<?= e($img) ?>" alt="" width="88" height="56" class="rounded object-fit-cover">
                      <?php else: ?><span class="admin-image-empty py-3">Chưa có ảnh</span><?php endif; ?>
                      <div><div class="fw-semibold"><?= e($s['tieu_de'] ?? '') ?></div><small class="text-muted"><?= e($s['nut_text'] ?: 'Không có nút') ?></small></div>
                    </div>
                  </td>
                  <td><?= (int)($s['vitri'] ?? 0) ?></td>
                  <td><span class="badge text-bg-<?= !empty($s['trangthai']) ? 'success' : 'secondary' ?>"><?= !empty($s['trangthai']) ? 'Đang hiển thị' : 'Đang ẩn' ?></span></td>
                  <td>
                    <div class="admin-table-actions">
                      <a class="btn btn-sm btn-outline-primary" href="index.php?action=slider&edit=<?= (int)$s['id'] ?>#slider-form">Sửa</a>
                      <form method="post" action="index.php">
                        <?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaislider"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <button class="btn btn-sm btn-outline-secondary"><?= !empty($s['trangthai']) ? 'Ẩn' : 'Hiện' ?></button>
                      </form>
                      <form method="post" action="index.php" onsubmit="return confirm('Xóa banner này?')">
                        <?= csrf_field() ?><input type="hidden" name="action" value="xoaslider"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$ds): ?><tr><td colspan="4" class="report-empty">Chưa có banner. Thêm banner để giới thiệu chương trình hoặc món nổi bật.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xxl-4" id="slider-form">
    <?php
      $editImage = !empty($edit['hinh_anh'])
          ? (preg_match('/^https?:\/\//i', (string)$edit['hinh_anh']) ? $edit['hinh_anh'] : img_url_dir($edit['hinh_anh'], 'slider'))
          : '';
    ?>
    <form method="post" action="index.php" enctype="multipart/form-data" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luuslider"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="admin-form-card-header">
        <h4><?= $edit ? 'Cập nhật banner' : 'Thêm banner' ?></h4>
        <p>Ảnh rõ nét, nội dung ngắn giúp khách dễ đọc.</p>
      </div>
      <div class="admin-form-card-body">
        <div class="mb-3">
          <label class="form-label" for="sliderTitle">Tiêu đề <span class="admin-required">*</span></label>
          <input id="sliderTitle" class="form-control" name="tieu_de" maxlength="255" required value="<?= e($edit['tieu_de'] ?? '') ?>" placeholder="Ví dụ: Ưu đãi cuối tuần">
        </div>
        <div class="mb-3">
          <label class="form-label" for="sliderDescription">Mô tả</label>
          <textarea id="sliderDescription" class="form-control" name="mota" rows="3" placeholder="Thông tin ngắn về ưu đãi hoặc món nổi bật"><?= e($edit['mota'] ?? '') ?></textarea>
        </div>
        <div class="admin-form-section">
          <h5 class="admin-form-section-title">Hình ảnh banner</h5>
          <div class="admin-image-preview mb-2" id="sliderImagePreview">
            <?php if ($editImage): ?><img src="<?= e($editImage) ?>" alt="Ảnh banner hiện tại">
            <?php else: ?><div class="admin-image-empty">Chưa chọn ảnh</div><?php endif; ?>
          </div>
          <label class="form-label" for="sliderImage">Ảnh <?= $edit ? '(không bắt buộc nếu giữ ảnh hiện tại)' : '<span class="admin-required">*</span>' ?></label>
          <input id="sliderImage" type="file" class="form-control" name="hinh_anh" accept="image/jpeg,image/png,image/webp" <?= $edit ? '' : 'required' ?>>
          <div class="form-text">JPG, PNG hoặc WEBP.</div>
        </div>
        <div class="admin-form-section">
          <h5 class="admin-form-section-title">Nút điều hướng</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="sliderButtonText">Nhãn nút</label>
              <input id="sliderButtonText" class="form-control" name="nut_text" maxlength="100" value="<?= e($edit['nut_text'] ?? '') ?>" placeholder="Xem ưu đãi">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="sliderPosition">Thứ tự hiển thị</label>
              <input id="sliderPosition" type="number" min="0" step="1" class="form-control" name="vitri" value="<?= (int)($edit['vitri'] ?? 0) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" for="sliderLink">Đường dẫn nút</label>
              <input id="sliderLink" class="form-control" name="nut_link" maxlength="255" value="<?= e($edit['nut_link'] ?? '#menu') ?>" placeholder="#menu hoặc index.php?action=...">
              <div class="form-text">Dùng #menu để dẫn tới thực đơn hoặc nhập đường dẫn nội bộ.</div>
            </div>
          </div>
        </div>
        <div class="form-check mt-3">
          <input id="sliderStatus" class="form-check-input" type="checkbox" name="trangthai" value="1" <?= !empty($edit['trangthai']) || empty($edit) ? 'checked' : '' ?>>
          <label class="form-check-label" for="sliderStatus">Hiển thị banner trên trang chủ</label>
        </div>
        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action"><?= $edit ? 'Lưu banner' : 'Tạo banner' ?></button>
          <?php if ($edit): ?><a class="btn btn-outline-secondary" href="index.php?action=slider">Hủy sửa</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('sliderImage').addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const image = document.createElement('img');
    image.alt = 'Ảnh banner được chọn';
    image.src = URL.createObjectURL(file);
    document.getElementById('sliderImagePreview').replaceChildren(image);
  });
</script>
<?php include 'inc/bottom.php'; ?>
