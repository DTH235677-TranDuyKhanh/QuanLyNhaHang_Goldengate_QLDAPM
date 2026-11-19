<?php
include 'inc/top.php';
$imgPreview = !empty($m['hinhanh']) ? '../uploads/monan/' . rawurlencode($m['hinhanh']) : '';
?>
<div class="admin-page-heading">
  <div>
    <h3><?= $m ? 'Cập nhật món ăn' : 'Thêm món ăn mới' ?></h3>
    <p>Thông tin này được dùng trên thực đơn của tất cả chi nhánh.</p>
  </div>
  <a class="btn btn-outline-secondary" href="index.php?action=monan">← Danh sách món</a>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <form method="post" action="index.php" enctype="multipart/form-data" class="card admin-form-card">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="luumon">
      <input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>">
      <div class="admin-form-card-header">
        <h4>Thông tin món</h4>
        <p>Các trường có dấu <span class="admin-required">*</span> là bắt buộc.</p>
      </div>
      <div class="admin-form-card-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label" for="foodName">Tên món <span class="admin-required">*</span></label>
            <input id="foodName" class="form-control" name="ten" maxlength="255" required
                   value="<?= e($m['tenmon'] ?? '') ?>" placeholder="Ví dụ: Phở bò đặc biệt">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="foodPrice">Giá bán <span class="admin-required">*</span></label>
            <div class="input-group">
              <input id="foodPrice" type="number" min="1" step="1000" class="form-control" name="gia" required
                     value="<?= (int)($m['giaban'] ?? 0) ?: '' ?>" placeholder="75000">
              <span class="input-group-text">đ</span>
            </div>
            <div class="form-text">Giá áp dụng trên toàn hệ thống.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="foodGroup">Nhóm món <span class="admin-required">*</span></label>
            <select id="foodGroup" class="form-select" name="nhom" required>
              <option value="">-- Chọn nhóm món --</option>
              <?php foreach ($nhoms as $n): ?>
                <option value="<?= (int)$n['id'] ?>" <?= (int)($m['nhom_id'] ?? 0) === (int)$n['id'] ? 'selected' : '' ?>><?= e($n['tennhom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="foodDescription">Mô tả thực đơn</label>
            <textarea id="foodDescription" class="form-control" name="mota" rows="4" maxlength="2000"
                      placeholder="Mô tả thành phần, khẩu phần hoặc lưu ý dị ứng..."><?= e($m['mota'] ?? '') ?></textarea>
            <div class="form-text">Mô tả ngắn gọn giúp khách hiểu rõ món trước khi đặt.</div>
          </div>
        </div>

        <div class="admin-form-section">
          <h5 class="admin-form-section-title">Ảnh món ăn</h5>
          <div class="row g-3 align-items-start">
            <div class="col-md-5">
              <div class="admin-image-preview" id="foodImagePreview">
                <?php if ($imgPreview): ?>
                  <img src="<?= e($imgPreview) ?>" alt="Ảnh món <?= e($m['tenmon']) ?>">
                <?php else: ?>
                  <div class="admin-image-empty">Chưa có ảnh món ăn</div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-7">
              <label class="form-label" for="foodImageInput">Chọn ảnh<?= $m ? '' : ' <span class="admin-required">*</span>' ?></label>
              <input id="foodImageInput" type="file" class="form-control" name="hinh"
                     accept="image/jpeg,image/png,image/webp" <?= $m ? '' : 'required' ?>>
              <div class="form-text">JPG, PNG hoặc WEBP. Ảnh mới sẽ thay ảnh cũ.</div>
            </div>
          </div>
        </div>

        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action"><?= $m ? 'Lưu thay đổi' : 'Tạo món ăn' ?></button>
          <a class="btn btn-outline-secondary" href="index.php?action=monan">Hủy</a>
        </div>
      </div>
    </form>
  </div>
  <aside class="col-xl-4">
    <div class="card admin-form-card">
      <div class="card-body">
        <h5 class="mb-2">Sau khi lưu</h5>
        <p class="text-muted small mb-3">Món mới có tồn kho mặc định bằng 0 tại từng chi nhánh.</p>
        <a class="btn btn-outline-primary w-100" href="index.php?action=tonkho">Đi đến quản lý tồn kho</a>
        <?php if ($m): ?>
          <hr>
          <div class="small text-muted">Trạng thái hiện tại</div>
          <span class="badge text-bg-<?= !empty($m['trangthai']) ? 'success' : 'secondary' ?> mt-1">
            <?= !empty($m['trangthai']) ? 'Đang bán' : 'Đang ẩn' ?>
          </span>
          <p class="form-text mt-2 mb-0">Thay đổi trạng thái tại danh sách món ăn.</p>
        <?php endif; ?>
      </div>
    </div>
  </aside>
</div>

<script>
  document.getElementById('foodImageInput').addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const preview = document.getElementById('foodImagePreview');
    const image = document.createElement('img');
    image.alt = 'Ảnh món ăn được chọn';
    image.src = URL.createObjectURL(file);
    preview.replaceChildren(image);
  });
</script>
<?php include 'inc/bottom.php'; ?>
