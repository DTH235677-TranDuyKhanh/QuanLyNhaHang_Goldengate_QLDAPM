<?php include 'inc/top.php'; ?>
<h3 class="mb-3">Nhóm món</h3>
<div class="row g-3">
  <div class="col-lg-7"><table class="table table-sm bg-white shadow-sm align-middle">
    <thead><tr><th>#</th><th>Tên nhóm</th><th>Số món</th><th></th></tr></thead>
    <?php foreach ($ds as $n): ?><tr><td><?= (int)$n['id'] ?></td><td><?= e($n['tennhom']) ?></td><td><?= NhomMon::demMon($n['id']) ?></td>
      <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="index.php?action=nhomon&edit=<?= (int)$n['id'] ?>">Sửa</a>
      <form class="d-inline" method="post" action="index.php" onsubmit="return confirm('Xóa nhóm này?')"><?= csrf_field() ?><input type="hidden" name="action" value="xoanhom"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr><?php endforeach; ?>
  </table></div>
  <div class="col-lg-5"><div class="card card-body border-0 shadow-sm">
    <h6><?= $edit ? 'Sửa nhóm' : 'Thêm nhóm' ?></h6>
    <form method="post" action="index.php" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="action" value="luunhom"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <input class="form-control" name="ten" required placeholder="Tên nhóm món" value="<?= e($edit['tennhom'] ?? '') ?>"><button class="btn btn-gg">Lưu</button></form>
  </div></div>
</div>
<?php include 'inc/bottom.php'; ?>
