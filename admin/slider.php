<?php include 'inc/top.php'; ?>
<h3 class="mb-3">Quản lý slider</h3>
<div class="row g-3">
  <div class="col-lg-8">
    <table class="table table-sm bg-white shadow-sm align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Ảnh</th>
          <th>Tiêu đề</th>
          <th>Vị trí</th>
          <th>Trạng thái</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ds as $s): ?>
          <?php
            $img = $s['hinh_anh'] ?? '';
            if ($img && !preg_match('/^https?:\/\//i', $img)) $img = img_url_dir($img, 'slider');
          ?>
          <tr>
            <td><?= (int)$s['id'] ?></td>
            <td style="width:110px">
              <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($s['tieu_de'] ?? '') ?>" style="width:100px;height:60px;object-fit:cover;border-radius:.5rem;">
              <?php else: ?><span class="text-muted small">Không có ảnh</span><?php endif; ?>
            </td>
            <td>
              <div class="fw-semibold"><?= e($s['tieu_de'] ?? '') ?></div>
              <div class="small text-muted"><?= e($s['nut_text'] ?? '') ?></div>
            </td>
            <td><?= (int)($s['vitri'] ?? 0) ?></td>
            <td><span class="badge text-bg-<?= !empty($s['trangthai']) ? 'success' : 'secondary' ?>"><?= !empty($s['trangthai']) ? 'Hiển thị' : 'Ẩn' ?></span></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="index.php?action=slider&edit=<?= (int)$s['id'] ?>">Sửa</a>
              <form class="d-inline" method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaislider"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= !empty($s['trangthai']) ? 'Ẩn' : 'Hiện' ?></button></form>
              <form class="d-inline" method="post" action="index.php" onsubmit="return confirm('Xóa slide này?');"><?= csrf_field() ?><input type="hidden" name="action" value="xoaslider"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="col-lg-4">
    <div class="card card-body border-0 shadow-sm">
      <h6><?= $edit ? 'Sửa slide' : 'Thêm slide mới' ?></h6>
      <form method="post" action="index.php" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="luuslider"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        <div class="mb-2">
          <label class="small">Tiêu đề</label>
          <input class="form-control" name="tieu_de" required value="<?= e($edit['tieu_de'] ?? '') ?>">
        </div>
        <div class="mb-2">
          <label class="small">Mô tả</label>
          <textarea class="form-control" name="mota" rows="3"><?= e($edit['mota'] ?? '') ?></textarea>
        </div>
        <div class="mb-2">
          <label class="small">Ảnh slide</label>
          <input type="file" class="form-control" name="hinh_anh" accept="image/*">
          <?php if (!empty($edit['hinh_anh'])): ?>
            <?php $img = preg_match('/^https?:\/\//i', (string)$edit['hinh_anh']) ? $edit['hinh_anh'] : img_url_dir($edit['hinh_anh'], 'slider'); ?>
            <img src="<?= e($img) ?>" class="mt-2" style="width:100%;max-height:160px;object-fit:cover;border-radius:.6rem;">
          <?php endif; ?>
        </div>
        <div class="row g-2 mb-2">
          <div class="col"><label class="small">Nút text</label><input class="form-control" name="nut_text" value="<?= e($edit['nut_text'] ?? '') ?>"></div>
          <div class="col"><label class="small">Vị trí</label><input type="number" class="form-control" name="vitri" value="<?= (int)($edit['vitri'] ?? 0) ?>"></div>
        </div>
        <div class="mb-2">
          <label class="small">Link nút</label>
          <input class="form-control" name="nut_link" value="<?= e($edit['nut_link'] ?? '#') ?>">
        </div>
        <div class="mb-3 form-check">
          <input class="form-check-input" type="checkbox" name="trangthai" value="1" <?= !empty($edit['trangthai']) || empty($edit) ? 'checked' : '' ?>>
          <label class="form-check-label">Hiển thị trên website</label>
        </div>
        <button class="btn btn-gg">Lưu</button>
        <?php if ($edit): ?><a class="btn btn-link" href="index.php?action=slider">Hủy</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
