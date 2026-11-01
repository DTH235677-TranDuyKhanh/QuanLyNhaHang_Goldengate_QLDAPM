<?php include 'inc/top.php'; $loaiLb = [1 => 'Quản trị tổng', 2 => 'Quản lý chi nhánh', 3 => 'Khách hàng']; ?>
<h3 class="mb-3">Người dùng</h3>
<div class="row g-3">
  <div class="col-lg-8"><table class="table table-sm bg-white shadow-sm align-middle">
    <thead><tr><th>Họ tên</th><th>Liên hệ</th><th>Vai trò</th><th>Chi nhánh</th><th>Trạng thái</th><th></th></tr></thead>
    <?php foreach ($ds as $u): ?><tr>
      <td><?= e($u['hoten']) ?></td><td><?= e($u['sodienthoai']) ?><br><small class="text-muted"><?= e($u['email']) ?></small></td>
      <td><?= $loaiLb[$u['loai']] ?? '' ?></td><td><?= e($u['tenchinhanh'] ?? '-') ?></td>
      <td><span class="badge text-bg-<?= $u['trangthai'] ? 'success' : 'danger' ?>"><?= $u['trangthai'] ? 'Hoạt động' : 'Đã khóa' ?></span></td>
      <td><?php if ((int)$u['id'] !== (int)$user['id']): ?><form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="khoanguoidung"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= $u['trangthai'] ? 'Khóa' : 'Mở khóa' ?></button></form><?php endif; ?></td>
    </tr><?php endforeach; ?>
  </table></div>
  <div class="col-lg-4"><div class="card card-body border-0 shadow-sm">
    <h6>Tạo tài khoản quản trị / quản lý</h6>
    <form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="luunhansu">
      <input class="form-control mb-2" name="hoten" placeholder="Họ tên" required>
      <input class="form-control mb-2" name="sdt" placeholder="Số điện thoại (10 số)" required pattern="0[0-9]{9}">
      <input class="form-control mb-2" type="email" name="email" placeholder="Email">
      <input class="form-control mb-2" type="password" name="matkhau" placeholder="Mật khẩu (≥ 6 ký tự)" minlength="6" required>
      <select class="form-select mb-2" name="loai"><option value="2">Quản lý chi nhánh</option><option value="1">Quản trị tổng</option></select>
      <select class="form-select mb-3" name="chinhanh"><option value="">- Chi nhánh (cho quản lý) -</option>
        <?php foreach ($cns as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['tenchinhanh']) ?></option><?php endforeach; ?></select>
      <button class="btn btn-gg w-100">Tạo tài khoản</button>
    </form>
  </div></div>
</div>
<?php include 'inc/bottom.php'; ?>
