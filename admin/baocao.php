<?php include 'inc/top.php'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h3 class="mb-1">Báo cáo doanh thu</h3>
    <p class="text-muted mb-0">Doanh thu chỉ tính trên các đơn không bị hủy.</p>
  </div>
  <div class="btn-group report-period" aria-label="Khoảng thời gian báo cáo">
    <?php foreach ([7, 30, 90] as $d): ?>
      <a class="btn btn-sm <?= $d === $days ? 'btn-gg' : 'btn-outline-secondary' ?>" href="index.php?action=baocao&days=<?= $d ?>"><?= $d ?> ngày</a>
    <?php endforeach; ?>
  </div>
</div>
<div class="row g-3">
  <?php if ($isAdmin): ?>
    <div class="col-12 col-xxl-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="report-card-title">Doanh thu theo chi nhánh</h5>
          <p class="report-card-hint">Tổng hợp trong <?= (int)$days ?> ngày gần nhất</p>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Chi nhánh</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead>
              <tbody>
                <?php foreach ($cnRev as $r): ?>
                  <tr><td class="fw-semibold"><?= e($r['tenchinhanh']) ?></td><td class="text-end"><?= (int)$r['sodon'] ?></td><td class="text-end fw-semibold"><?= money($r['doanhthu']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$cnRev): ?><tr><td colspan="3" class="report-empty">Chưa có dữ liệu trong khoảng thời gian này.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="col-12 <?= $isAdmin ? 'col-xxl-6' : '' ?>">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="report-card-title">Doanh thu theo ngày</h5>
        <p class="report-card-hint">Diễn biến số đơn và doanh thu mỗi ngày</p>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Ngày</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead>
            <tbody>
              <?php foreach ($ngay as $r): ?>
                <tr><td><?= date('d/m/Y', strtotime($r['ngay'])) ?></td><td class="text-end"><?= (int)$r['sodon'] ?></td><td class="text-end fw-semibold"><?= money($r['doanhthu']) ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$ngay): ?><tr><td colspan="3" class="report-empty">Chưa có dữ liệu trong khoảng thời gian này.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class="report-card-title">Top 10 món bán chạy</h5>
        <p class="report-card-hint">Xếp hạng theo số phần đã bán trong <?= (int)$days ?> ngày gần nhất</p>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th style="width:5rem">Hạng</th><th>Món ăn</th><th class="text-end">Số phần</th><th class="text-end">Doanh thu</th></tr></thead>
            <tbody>
              <?php foreach ($top as $i => $r): ?>
                <tr><td><span class="badge rounded-pill text-bg-light"><?= (int)$i + 1 ?></span></td><td class="fw-semibold"><?= e($r['tenmon']) ?></td><td class="text-end"><?= (int)$r['sl'] ?></td><td class="text-end fw-semibold"><?= money($r['doanhthu']) ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$top): ?><tr><td colspan="4" class="report-empty">Chưa có dữ liệu bán món trong khoảng thời gian này.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
