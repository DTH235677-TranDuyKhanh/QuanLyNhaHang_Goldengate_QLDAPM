<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Nhóm món</h3>
    <p>Phân loại món để khách tìm thực đơn nhanh hơn.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Danh sách nhóm <span class="badge text-bg-light"><?= count($ds) ?></span></h5>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th style="width:5rem">Mã</th><th>Tên nhóm</th><th class="text-end">Số món</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ds as $n): ?>
                <?php $soMon = NhomMon::demMon($n['id']); ?>
                <tr>
                  <td class="text-muted">#<?= (int)$n['id'] ?></td>
                  <td class="fw-semibold"><?= e($n['tennhom']) ?></td>
                  <td class="text-end"><?= $soMon ?></td>
                  <td>
                    <div class="admin-table-actions justify-content-end">
                      <a class="btn btn-sm btn-outline-primary" href="index.php?action=nhomon&edit=<?= (int)$n['id'] ?>#group-form">Sửa</a>
                      <form method="post" action="index.php" onsubmit="return confirm('Xóa nhóm món này? Chỉ nhóm chưa có món mới xóa được.')">
                        <?= csrf_field() ?><input type="hidden" name="action" value="xoanhom"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" <?= $soMon ? 'disabled title="Nhóm đang có món"' : '' ?>>Xóa</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$ds): ?><tr><td colspan="4" class="report-empty">Chưa có nhóm món nào.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-5" id="group-form">
    <form method="post" action="index.php" class="card admin-form-card">
      <?= csrf_field() ?><input type="hidden" name="action" value="luunhom"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="admin-form-card-header">
        <h4><?= $edit ? 'Đổi tên nhóm' : 'Tạo nhóm món' ?></h4>
        <p><?= $edit ? 'Tên nhóm mới sẽ hiển thị trên thực đơn.' : 'Ví dụ: Món khai vị, món chính hoặc đồ uống.' ?></p>
      </div>
      <div class="admin-form-card-body">
        <label class="form-label" for="groupName">Tên nhóm <span class="admin-required">*</span></label>
        <input id="groupName" class="form-control" name="ten" maxlength="50" required autofocus value="<?= e($edit['tennhom'] ?? '') ?>" placeholder="Nhập tên nhóm">
        <div class="admin-form-actions">
          <button class="btn btn-gg btn-primary-action"><?= $edit ? 'Lưu tên nhóm' : 'Tạo nhóm' ?></button>
          <?php if ($edit): ?><a class="btn btn-outline-secondary" href="index.php?action=nhomon">Hủy sửa</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
