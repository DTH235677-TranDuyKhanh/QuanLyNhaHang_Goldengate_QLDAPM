<?php include 'inc/top.php'; ?>
<?php
$imgPreview = !empty($m['hinhanh']) ? '../uploads/monan/' . rawurlencode($m['hinhanh']) : '';
?>
<div class="food-form-shell">
  <div class="food-form-card">
    <div class="food-form-header">
      <div>
        <div class="eyebrow">Quản lý thực đơn</div>
        <h3 class="mb-0"><?= $m ? 'Sửa món ăn' : 'Thêm món ăn' ?></h3>
      </div>
      <a class="back-link" href="index.php?action=monan">← Quay lại</a>
    </div>

    <form method="post" action="index.php" enctype="multipart/form-data" class="food-form-body">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="luumon">
      <input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>">

      <div class="mb-4">
        <label class="form-label">Tên món <span class="required">*</span></label>
        <input class="form-control custom-input" name="ten" required value="<?= e($m['tenmon'] ?? '') ?>" placeholder="Ví dụ: Súp cua trứng bạc">
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Giá bán (đ) <span class="required">*</span></label>
          <input type="number" min="1000" step="1000" class="form-control custom-input" name="gia" required value="<?= (int)($m['giaban'] ?? 0) ?: '' ?>" placeholder="70000">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nhóm món <span class="required">*</span></label>
          <select class="form-select custom-input" name="nhom" required>
            <?php foreach ($nhoms as $n): ?>
              <option value="<?= (int)$n['id'] ?>" <?= (int)($m['nhom_id'] ?? 0) === (int)$n['id'] ? 'selected' : '' ?>><?= e($n['tennhom']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Mô tả</label>
        <textarea class="form-control custom-input" name="mota" rows="4" placeholder="Mô tả ngắn gọn về món ăn..."><?= e($m['mota'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label">Ảnh món ăn <span class="small text-muted">(JPG/PNG/WEBP, tối đa 2MB)</span></label>
        <div class="upload-box">
          <div class="upload-preview <?php if (!$imgPreview): ?>empty<?php endif; ?>">
            <?php if ($imgPreview): ?>
              <img src="<?= e($imgPreview) ?>" alt="Preview món ăn">
            <?php else: ?>
              <div class="upload-placeholder">
                <span>📷</span>
                <small>Chưa có ảnh</small>
              </div>
            <?php endif; ?>
          </div>
          <div class="upload-input-wrap">
            <input type="file" class="form-control custom-file" name="hinh" accept="image/jpeg,image/png,image/webp" id="foodImageInput">
            <label for="foodImageInput" class="file-button">Choose File</label>
            <span class="file-name" id="foodFileName">No file chosen</span>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-gg">Lưu món</button>
      </div>
    </form>
  </div>
</div>

<style>
  body {
    background: #f3efe8;
  }
  .food-form-shell {
    padding: 18px 0 10px;
  }
  .food-form-card {
    background: rgba(255,255,255,0.55);
    border: 1px solid rgba(31,26,20,0.08);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(62,44,20,0.06);
    padding: 28px 30px 24px;
    max-width: 100%;
    width: 100%;
  }
  .food-form-body {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px 28px;
  }
  .food-form-body > div,
  .food-form-body > .form-actions {
    grid-column: 1 / -1;
  }
  .food-form-body .row {
    grid-column: 1 / -1;
  }
  .food-form-body .upload-box {
    min-height: 100%;
  }
  .food-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
  }
  .eyebrow {
    color: #8d6e3b;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .food-form-header h3 {
    font-weight: 800;
    font-size: clamp(1.7rem, 2vw, 2.3rem);
    color: #1f1b17;
  }
  .back-link {
    color: #8a6a2a;
    text-decoration: none;
    font-weight: 600;
  }
  .back-link:hover { text-decoration: underline; }
  .food-form-body {
    width: 100%;
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 18px 28px;
    align-items: start;
  }
  .food-form-body > .mb-4,
  .food-form-body > .row,
  .food-form-body > .form-actions {
    grid-column: 1 / -1;
  }
  .food-form-body .row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin: 0;
  }
  .form-label {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: #2d2824;
    margin-bottom: 8px;
  }
  .required {
    color: #c3423f;
  }
  .custom-input {
    border: 1px solid #d9d0c2;
    background: rgba(255,255,255,0.6);
    border-radius: 12px;
    min-height: 48px;
    padding: 0.8rem 0.9rem;
    color: #2d2824;
    box-shadow: none;
  }
  .custom-input:focus {
    border-color: #d5a740;
    box-shadow: 0 0 0 0.25rem rgba(184,134,11,0.14);
    background: #fff;
  }
  .custom-input::placeholder {
    color: #9b8f7d;
  }
  .upload-box {
    border: 1px solid #d9d0c2;
    border-radius: 14px;
    background: rgba(255,255,255,0.45);
    padding: 16px;
  }
  .upload-preview {
    width: 100%;
    min-height: 160px;
    border: 1px dashed #d3bf96;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(233,220,193,0.32), rgba(255,255,255,0.1));
    overflow: hidden;
    margin-bottom: 12px;
  }
  .upload-preview.empty {
    min-height: 120px;
  }
  .upload-preview img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
  }
  .upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #7d6d53;
  }
  .upload-placeholder span { font-size: 2rem; }
  .upload-input-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .custom-file {
    display: none;
  }
  .file-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    border: 1px solid #d9d0c2;
    background: #f7f1e6;
    color: #3d332c;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
  }
  .file-button:hover {
    background: #f2e6d4;
  }
  .file-name {
    color: #756d62;
    font-size: .95rem;
  }
  .form-actions {
    margin-top: 8px;
  }
  .btn-gg {
    background: linear-gradient(135deg, #bb8d2b 0%, #9f700a 100%);
    border: none;
    color: #fff;
    border-radius: 12px;
    padding: .8rem 1.6rem;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(159,112,10,.25);
  }
  .btn-gg:hover {
    background: linear-gradient(135deg, #a77106 0%, #7e5603 100%);
    color: #fff;
  }
  @media (max-width: 767px) {
    .food-form-card { padding: 18px 16px; }
    .food-form-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .upload-input-wrap { align-items: flex-start; }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('foodImageInput');
    const nameBox = document.getElementById('foodFileName');
    if (input && nameBox) {
      input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        nameBox.textContent = file ? file.name : 'No file chosen';
      });
    }
  });
</script>
<?php include 'inc/bottom.php'; ?>
