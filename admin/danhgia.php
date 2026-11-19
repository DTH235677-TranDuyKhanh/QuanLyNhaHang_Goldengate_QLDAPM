<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div><h3>Phản hồi và đánh giá</h3><p>Quản lý đánh giá được gửi từ khách đã hoàn thành đơn hàng.</p></div>
</div>
<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Ngày</th><th>Khách hàng</th><th>Món</th><th>Đơn</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($ds as $review): ?>
        <tr>
          <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($review['ngaytao'])) ?></td>
          <td><?= e($review['hoten']) ?></td>
          <td><?= e($review['tenmon']) ?></td>
          <td><?= maDon($review['donhang_ma']) ?></td>
          <td class="text-nowrap"><?= str_repeat('★', (int)$review['so_sao']) . str_repeat('☆', 5 - (int)$review['so_sao']) ?></td>
          <td><?= nl2br(e($review['noi_dung'])) ?></td>
          <td><span class="badge text-bg-<?= $review['trangthai'] ? 'success' : 'secondary' ?>"><?= $review['trangthai'] ? 'Hiển thị' : 'Đã ẩn' ?></span></td>
          <td><form method="post" action="index.php" class="d-grid gap-2">
            <?= csrf_field() ?><input type="hidden" name="action" value="doitrangthaidanhgia"><input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
            <button class="btn btn-sm btn-outline-secondary"><?= $review['trangthai'] ? 'Ẩn' : 'Hiện' ?></button>
          </form></td>
        </tr>
        <tr>
          <td colspan="8">
            <?php if (!empty($review['phan_hoi'])): ?>
              <div class="small text-muted mb-2">Đã phản hồi lúc <?= date('d/m/Y H:i', strtotime($review['phan_hoi_luc'])) ?>: <?= nl2br(e($review['phan_hoi'])) ?></div>
            <?php endif; ?>
            <form method="post" action="index.php" class="d-flex gap-2 align-items-start">
              <?= csrf_field() ?><input type="hidden" name="action" value="phanhoidanhgia"><input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
              <label class="visually-hidden" for="reply-<?= (int)$review['id'] ?>">Phản hồi đánh giá của <?= e($review['hoten']) ?></label>
              <textarea id="reply-<?= (int)$review['id'] ?>" class="form-control form-control-sm" name="phan_hoi" rows="2" maxlength="1000" placeholder="Viết phản hồi từ nhà hàng..."><?= e($review['phan_hoi'] ?? '') ?></textarea>
              <button class="btn btn-sm btn-primary text-nowrap">Lưu phản hồi</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ds): ?><tr><td colspan="8" class="report-empty">Chưa có đánh giá nào.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php include 'inc/bottom.php'; ?>
