<?php include 'inc/top.php'; $fee = (int)cfg('ship_fee'); $free = (int)cfg('free_ship_from'); ?>
<h4 class="section-title mb-3">Thông tin đặt món</h4>
<div class="row g-4">
  <div class="col-lg-7">
    <form method="post" action="index.php" class="card card-body border-0 shadow-sm" id="fco">
      <?= csrf_field() ?><input type="hidden" name="action" value="xlthanhtoan">
      <?php if (!$user): ?><div class="alert alert-info py-2 small">Bạn có thể đặt món không cần tài khoản. <a href="index.php?action=dangnhap">Đăng nhập</a> để được gợi ý món riêng và xem lại lịch sử.</div><?php endif; ?>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Họ tên *</label><input class="form-control" name="hoten" required value="<?= e($f['hoten']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Số điện thoại *</label><input class="form-control" name="sdt" required pattern="0[0-9]{9}" value="<?= e($f['sdt']) ?>"></div>
        <div class="col-12">
          <label class="form-label d-block">Hình thức nhận món *</label>
          <?php foreach ([1 => 'Dùng tại quán', 2 => 'Mang đi', 3 => 'Giao hàng'] as $k => $lb): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="hinhthuc" id="ht<?= $k ?>" value="<?= $k ?>" <?= (int)$f['hinhthuc'] === $k ? 'checked' : '' ?>>
              <label class="form-check-label" for="ht<?= $k ?>"><?= $lb ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="col-12" id="boxdc"><label class="form-label">Địa chỉ giao hàng</label><input class="form-control" name="diachi" value="<?= e($f['diachi']) ?>"></div>
        <div class="col-12"><label class="form-label">Ghi chú</label><input class="form-control" name="ghichu" maxlength="250" value="<?= e($f['ghichu']) ?>" placeholder="Ví dụ: ít cay, không hành..."></div>
      </div>
      <button class="btn btn-gg btn-lg mt-3">Xác nhận đặt món</button>
    </form>
  </div>
  <div class="col-lg-5">
    <div class="card card-body border-0 shadow-sm">
      <h6>Chi nhánh: <?= e($cb['tenchinhanh']) ?></h6><hr class="my-2">
      <?php foreach ($dong as $d): ?>
        <div class="d-flex justify-content-between small mb-1"><span><?= e($d['tenmon']) ?> × <?= (int)$d['soluong'] ?></span><span><?= money($d['thanhtien']) ?></span></div>
      <?php endforeach; ?>
      <hr class="my-2">
      <div class="d-flex justify-content-between"><span>Tạm tính</span><span id="tam" data-v="<?= (int)$tam ?>"><?= money($tam) ?></span></div>
      <div class="d-flex justify-content-between"><span>Phí giao hàng</span><span id="phi">0đ</span></div>
      <div class="d-flex justify-content-between fw-bold fs-5"><span>Tổng cộng</span><span class="price" id="tong"><?= money($tam) ?></span></div>
      <small class="text-muted">Giao hàng: <?= money($fee) ?>, miễn phí từ <?= money($free) ?>. Thanh toán khi nhận món.</small>
    </div>
  </div>
</div>
<script>
(function(){
  var fee=<?= $fee ?>, free=<?= $free ?>, tam=parseInt(document.getElementById('tam').dataset.v,10);
  function fmt(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'đ';}
  function upd(){
    var ht=document.querySelector('input[name=hinhthuc]:checked').value;
    var p=(ht==='3' && tam<free)?fee:0;
    document.getElementById('boxdc').style.display=(ht==='3')?'block':'none';
    document.getElementById('phi').textContent=fmt(p);
    document.getElementById('tong').textContent=fmt(tam+p);
  }
  document.querySelectorAll('input[name=hinhthuc]').forEach(function(r){r.addEventListener('change',upd);});
  upd();
})();
</script>
<?php include 'inc/bottom.php'; ?>
