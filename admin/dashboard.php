<?php include 'inc/top.php'; $maxRev = 1; foreach ($cnRev as $r) $maxRev = max($maxRev, (float)$r['doanhthu']); ?>
<h3 class="mb-4">Tổng quan <?= $isAdmin ? 'toàn chuỗi' : 'chi nhánh' ?></h3>
<div class="row g-3 mb-4">
  <?php foreach ([['Đơn hôm nay', $tq['don_homnay'], 'primary'], ['Doanh thu hôm nay', money($tq['doanhthu_homnay']), 'success'],
                  ['Đơn chờ xác nhận', $tq['don_cho'], 'warning'], ['Món sắp hết (≤5)', $tq['sap_het'], 'danger']] as [$lb, $v, $c]): ?>
    <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body">
      <div class="text-muted small"><?= $lb ?></div><div class="fs-3 fw-bold text-<?= $c ?>"><?= $v ?></div>
    </div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <?php if ($isAdmin): ?>
  <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body">
    <h6>Doanh thu 30 ngày theo chi nhánh</h6>
    <?php foreach ($cnRev as $r): ?>
      <div class="mb-2"><div class="d-flex justify-content-between small"><span><?= e($r['tenchinhanh']) ?> (<?= (int)$r['sodon'] ?> đơn)</span><strong><?= money($r['doanhthu']) ?></strong></div>
        <div class="progress" style="height:8px"><div class="progress-bar bg-warning" style="width:<?= round($r['doanhthu'] / $maxRev * 100) ?>%"></div></div></div>
    <?php endforeach; ?>
  </div></div></div>
  <?php endif; ?>
  <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body">
    <h6>Top món bán chạy (30 ngày)</h6>
    <table class="table table-sm mb-0"><?php foreach ($top as $t): ?><tr><td><?= e($t['tenmon']) ?></td><td class="text-end"><?= (int)$t['sl'] ?> phần</td><td class="text-end"><?= money($t['doanhthu']) ?></td></tr><?php endforeach; ?>
    <?php if (!$top): ?><tr><td class="text-muted">Chưa có dữ liệu.</td></tr><?php endif; ?></table>
  </div></div></div>
  <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body">
    <h6>Đơn chờ xác nhận</h6>
    <table class="table table-sm mb-0"><?php foreach ($dons as $d): ?><tr><td><a href="index.php?action=chitietdon&id=<?= (int)$d['id'] ?>"><?= maDon($d['id']) ?></a></td><td><?= e($d['tenchinhanh']) ?></td><td class="text-end"><?= money($d['tongtien']) ?></td></tr><?php endforeach; ?>
    <?php if (!$dons): ?><tr><td class="text-muted">Không có đơn chờ.</td></tr><?php endif; ?></table>
  </div></div></div>
  <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body">
    <h6>Cảnh báo tồn kho thấp</h6>
    <table class="table table-sm mb-0"><?php foreach ($saphet as $s): ?><tr><td><?= e($s['tenmon']) ?></td><td><?= e($s['tenchinhanh']) ?></td><td class="text-end text-danger"><?= (int)$s['soluongton'] ?></td></tr><?php endforeach; ?>
    <?php if (!$saphet): ?><tr><td class="text-muted">Tồn kho ổn định.</td></tr><?php endif; ?></table>
  </div></div></div>
</div>
<?php include 'inc/bottom.php'; ?>
