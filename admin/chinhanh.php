<?php include 'inc/top.php'; ?>
<h3 class="mb-3">Quản lý chi nhánh</h3>
<div class="row g-3">
  <div class="col-lg-8"><table class="table table-sm bg-white shadow-sm align-middle">
    <thead><tr><th>#</th><th>Chi nhánh</th><th>Địa chỉ</th><th>SĐT</th><th>Giờ mở cửa</th><th>Trạng thái</th><th></th></tr></thead>
    <?php foreach ($ds as $c): ?><tr>
      <td><?= (int)$c['id'] ?></td><td><?= e($c['tenchinhanh']) ?></td><td><?= e($c['diachi']) ?></td><td><?= e($c['sodienthoai']) ?></td>
      <td><?= substr($c['giomocua'], 0, 5) ?>-<?= substr($c['giodongcua'], 0, 5) ?></td>
      <td><span class="badge text-bg-<?= $c['trangthai'] ? 'success' : 'secondary' ?>"><?= $c['trangthai'] ? 'Hoạt động' : 'Tạm đóng' ?></span></td>
      <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="index.php?action=chinhanh&edit=<?= (int)$c['id'] ?>">Sửa</a>
        <form class="d-inline" method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaicn"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= $c['trangthai'] ? 'Đóng' : 'Mở' ?></button></form></td>
    </tr><?php endforeach; ?>
  </table></div>
  <div class="col-lg-4"><div class="card card-body border-0 shadow-sm">
    <h6><?= $edit ? 'Sửa chi nhánh' : 'Thêm chi nhánh' ?></h6>
    <form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="luuchinhanh"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="mb-2"><input class="form-control" name="ten" placeholder="Tên chi nhánh" required value="<?= e($edit['tenchinhanh'] ?? '') ?>"></div>
      <div class="mb-2"><input class="form-control" name="diachi" placeholder="Địa chỉ" required value="<?= e($edit['diachi'] ?? '') ?>"></div>
      <div class="mb-2"><input class="form-control" name="sdt" placeholder="Số điện thoại" value="<?= e($edit['sodienthoai'] ?? '') ?>"></div>
      <div class="row g-2 mb-3"><div class="col"><label class="small">Mở cửa</label><input type="time" class="form-control" name="mo" value="<?= e(substr($edit['giomocua'] ?? '10:00', 0, 5)) ?>"></div>
        <div class="col"><label class="small">Đóng cửa</label><input type="time" class="form-control" name="dong" value="<?= e(substr($edit['giodongcua'] ?? '22:00', 0, 5)) ?>"></div></div>
      <button class="btn btn-gg">Lưu</button> <?php if ($edit): ?><a class="btn btn-link" href="index.php?action=chinhanh">Hủy</a><?php endif; ?>
    </form>
  </div></div>
</div>
<?php include 'inc/bottom.php'; ?>
