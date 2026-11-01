<?php include 'inc/top.php'; ?>
<h4 class="section-title mb-3">Tra cứu đơn hàng</h4>
<form method="post" action="index.php?action=tracuu" class="row g-2 mb-4">
  <?= csrf_field() ?>
  <div class="col-md-4"><input class="form-control" name="ma" placeholder="Mã đơn (VD: GG000012)" required></div>
  <div class="col-md-4"><input class="form-control" name="sdt" placeholder="Số điện thoại đặt món" required></div>
  <div class="col-md-2"><button class="btn btn-gg w-100">Tra cứu</button></div>
</form>
<?php if ($d): [$lb, $cl] = trangthai_don($d['trangthai']); ?>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <h5><?= maDon($d['id']) ?> <span class="badge text-bg-<?= $cl ?>"><?= $lb ?></span></h5>
    <p class="text-muted mb-2"><?= e($d['tenchinhanh']) ?> · <?= hinhthuc_don($d['hinhthuc']) ?> · <?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></p>
    <?php foreach ($ct as $c): ?><div class="d-flex justify-content-between"><span><?= e($c['tenmon']) ?> × <?= (int)$c['soluong'] ?></span><span><?= money($c['thanhtien']) ?></span></div><?php endforeach; ?>
    <hr><div class="d-flex justify-content-between fw-bold"><span>Tổng cộng</span><span><?= money($d['tongtien']) ?></span></div>
  </div></div>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
