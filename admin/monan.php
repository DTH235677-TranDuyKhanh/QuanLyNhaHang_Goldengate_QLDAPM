<?php include 'inc/top.php'; ?>
<div class="d-flex justify-content-between mb-3"><h3>Món ăn</h3><a class="btn btn-gg" href="index.php?action=monan_form">+ Thêm món</a></div>
<table class="table table-sm bg-white shadow-sm align-middle">
  <thead><tr><th style="width:64px">Ảnh</th><th>Tên món</th><th>Nhóm</th><th class="text-end">Giá</th><th class="text-end">Xem</th><th class="text-end">Đã gọi</th><th>Trạng thái</th><th></th></tr></thead>
  <?php foreach ($ds as $m): $img = $m['hinhanh'] ? '../uploads/monan/' . rawurlencode($m['hinhanh']) : null; ?><tr>
    <td><?php if ($img): ?><img src="<?= e($img) ?>" width="48" height="48" style="object-fit:cover" class="rounded"><?php else: ?>🍽️<?php endif; ?></td>
    <td><?= e($m['tenmon']) ?></td><td><?= e($m['tennhom']) ?></td><td class="text-end"><?= money($m['giaban']) ?></td>
    <td class="text-end"><?= (int)$m['luotxem'] ?></td><td class="text-end"><?= (int)$m['luotgoi'] ?></td>
    <td><span class="badge text-bg-<?= $m['trangthai'] ? 'success' : 'secondary' ?>"><?= $m['trangthai'] ? 'Đang bán' : 'Ẩn' ?></span></td>
    <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="index.php?action=monan_form&id=<?= (int)$m['id'] ?>">Sửa</a>
      <form class="d-inline" method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaimon"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= $m['trangthai'] ? 'Ẩn' : 'Hiện' ?></button></form></td>
  </tr><?php endforeach; ?>
</table>
<?php include 'inc/bottom.php'; ?>
