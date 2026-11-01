<?php include 'inc/top.php'; ?>
<h3 class="mb-3"><?= $m ? 'Sửa món ăn' : 'Thêm món ăn' ?></h3>
<form method="post" action="index.php" enctype="multipart/form-data" class="card card-body border-0 shadow-sm" style="max-width:720px">
  <?= csrf_field() ?><input type="hidden" name="action" value="luumon"><input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>">
  <div class="mb-3"><label class="form-label">Tên món *</label><input class="form-control" name="ten" required value="<?= e($m['tenmon'] ?? '') ?>"></div>
  <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Giá bán (đ) *</label><input type="number" min="1000" step="1000" class="form-control" name="gia" required value="<?= (int)($m['giaban'] ?? 0) ?: '' ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nhóm món *</label><select class="form-select" name="nhom" required>
      <?php foreach ($nhoms as $n): ?><option value="<?= (int)$n['id'] ?>" <?= (int)($m['nhom_id'] ?? 0) === (int)$n['id'] ? 'selected' : '' ?>><?= e($n['tennhom']) ?></option><?php endforeach; ?></select></div></div>
  <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="mota" rows="3"><?= e($m['mota'] ?? '') ?></textarea></div>
  <div class="mb-3"><label class="form-label">Ảnh (JPG/PNG/WEBP, tối đa 2MB)</label><input type="file" class="form-control" name="hinh" accept="image/jpeg,image/png,image/webp">
    <?php if (!empty($m['hinhanh'])): ?><img src="../uploads/monan/<?= e(rawurlencode($m['hinhanh'])) ?>" height="80" class="mt-2 rounded"><?php endif; ?></div>
  <div><button class="btn btn-gg">Lưu món</button> <a class="btn btn-link" href="index.php?action=monan">Quay lại</a></div>
</form>
<?php include 'inc/bottom.php'; ?>
