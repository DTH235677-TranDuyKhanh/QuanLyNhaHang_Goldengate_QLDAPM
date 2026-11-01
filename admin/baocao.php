<?php include 'inc/top.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0">Báo cáo doanh thu</h3>
  <div class="btn-group"><?php foreach ([7, 30, 90] as $d): ?><a class="btn btn-sm <?= $d === $days ? 'btn-gg' : 'btn-outline-secondary' ?>" href="index.php?action=baocao&days=<?= $d ?>"><?= $d ?> ngày</a><?php endforeach; ?></div></div>
<p class="text-muted small">Doanh thu tính trên các đơn không bị hủy.</p>
<div class="row g-3">
  <?php if ($isAdmin): ?><div class="col-lg-6"><div class="card card-body border-0 shadow-sm"><h6>Theo chi nhánh</h6>
    <table class="table table-sm mb-0"><thead><tr><th>Chi nhánh</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead>
    <?php foreach ($cnRev as $r): ?><tr><td><?= e($r['tenchinhanh']) ?></td><td class="text-end"><?= (int)$r['sodon'] ?></td><td class="text-end"><?= money($r['doanhthu']) ?></td></tr><?php endforeach; ?></table></div></div><?php endif; ?>
  <div class="col-lg-6"><div class="card card-body border-0 shadow-sm"><h6>Theo ngày</h6>
    <table class="table table-sm mb-0"><thead><tr><th>Ngày</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead>
    <?php foreach ($ngay as $r): ?><tr><td><?= date('d/m/Y', strtotime($r['ngay'])) ?></td><td class="text-end"><?= (int)$r['sodon'] ?></td><td class="text-end"><?= money($r['doanhthu']) ?></td></tr><?php endforeach; ?>
    <?php if (!$ngay): ?><tr><td colspan="3" class="text-muted">Chưa có dữ liệu.</td></tr><?php endif; ?></table></div></div>
  <div class="col-12"><div class="card card-body border-0 shadow-sm"><h6>Top 10 món</h6>
    <table class="table table-sm mb-0"><thead><tr><th>Món</th><th class="text-end">Số phần</th><th class="text-end">Doanh thu</th></tr></thead>
    <?php foreach ($top as $r): ?><tr><td><?= e($r['tenmon']) ?></td><td class="text-end"><?= (int)$r['sl'] ?></td><td class="text-end"><?= money($r['doanhthu']) ?></td></tr><?php endforeach; ?></table></div></div>
</div>
<?php include 'inc/bottom.php'; ?>
