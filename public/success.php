<?php include 'inc/top.php'; ?>
<div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
  <div style="font-size:3rem">✅</div>
  <h3>Đặt món thành công!</h3>
  <p class="mb-1">Mã đơn của bạn: <strong class="text-gg fs-4"><?= maDon($d['id']) ?></strong></p>
  <p class="text-muted">Chi nhánh <?= e($d['tenchinhanh']) ?> · <?= hinhthuc_don($d['hinhthuc']) ?> · SĐT <?= e($d['sodt_nhan']) ?><br>Hãy lưu mã đơn để tra cứu trạng thái.</p>
  <table class="table table-sm w-auto mx-auto text-start">
    <?php foreach ($ct as $c): ?><tr><td><?= e($c['tenmon']) ?> × <?= (int)$c['soluong'] ?></td><td class="text-end"><?= money($c['thanhtien']) ?></td></tr><?php endforeach; ?>
    <?php if ($d['phigiao'] > 0): ?><tr><td>Phí giao hàng</td><td class="text-end"><?= money($d['phigiao']) ?></td></tr><?php endif; ?>
    <tr class="fw-bold"><td>Tổng cộng</td><td class="text-end"><?= money($d['tongtien']) ?></td></tr>
  </table>
  <a href="index.php" class="btn btn-gg">Tiếp tục xem thực đơn</a>
  <a href="index.php?action=tracuu" class="btn btn-outline-secondary">Tra cứu đơn</a>
</div></div>
<?php include 'inc/bottom.php'; ?>
