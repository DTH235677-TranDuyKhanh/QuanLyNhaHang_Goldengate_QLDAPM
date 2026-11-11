<?php
include 'inc/top.php';
$bank = cfg('bank_transfer') ?: [];
$reference = maDon($d['id']);
$tlv = function ($id, $value) {
    $value = (string)$value;
    return $id . str_pad((string)strlen($value), 2, '0', STR_PAD_LEFT) . $value;
};
$accountInfo = $tlv('00', $bank['bank_code'] ?? '') . $tlv('01', $bank['account_number'] ?? '');
$merchantInfo = $tlv('00', 'A000000727') . $tlv('01', $accountInfo) . $tlv('02', 'QRIBFTTA');
$additionalInfo = $tlv('08', $reference);
$qrPayload = '000201010212' . $tlv('38', $merchantInfo) . $tlv('53', '704')
    . $tlv('54', (string)(int)$d['tongtien']) . $tlv('58', 'VN') . $tlv('62', $additionalInfo) . '6304';
$crc = 0xFFFF;
for ($i = 0, $length = strlen($qrPayload); $i < $length; $i++) {
    $crc ^= ord($qrPayload[$i]) << 8;
    for ($bit = 0; $bit < 8; $bit++) {
        $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) & 0xFFFF : ($crc << 1) & 0xFFFF;
    }
}
$qrPayload .= strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
$active = $d['trangthai_tt'] === 'pending' && (int)$d['trangthai'] === 0;
?>
<h4 class="section-title mb-3">Thanh toán đơn <?= e($reference) ?></h4>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body">
      <?php if ($active): ?>
        <div class="alert alert-info">Vui lòng chuyển đúng số tiền và nội dung bên dưới. Nhân viên sẽ kiểm tra sao kê và xác nhận thủ công.</div>
        <div class="row g-4 align-items-center">
          <div class="col-md-5 text-center">
            <div id="bankTransferQr" class="d-inline-block border rounded p-2" role="img" aria-label="Mã QR chuyển khoản Vietcombank"></div>
            <small id="bankTransferQrStatus" class="d-block text-muted mt-2">Đang tạo mã QR…</small>
          </div>
          <div class="col-md-7">
            <dl class="row mb-0">
              <dt class="col-5">Ngân hàng</dt><dd class="col-7"><?= e($bank['bank_name'] ?? '') ?></dd>
              <dt class="col-5">Số tài khoản</dt><dd class="col-7 fw-semibold"><?= e($bank['account_number'] ?? '') ?></dd>
              <dt class="col-5">Chủ tài khoản</dt><dd class="col-7"><?= e($bank['account_name'] ?? '') ?></dd>
              <dt class="col-5">Số tiền</dt><dd class="col-7 fw-bold text-danger"><?= money($d['tongtien']) ?></dd>
              <dt class="col-5">Nội dung</dt><dd class="col-7 fw-semibold"><?= e($reference) ?></dd>
              <?php if (!empty($d['thanhtoan_het_han'])): ?>
                <dt class="col-5">Hạn thanh toán</dt><dd class="col-7"><?= date('d/m/Y H:i', strtotime($d['thanhtoan_het_han'])) ?></dd>
              <?php endif; ?>
            </dl>
          </div>
        </div>
      <?php elseif ($d['trangthai_tt'] === 'paid'): ?>
        <div class="alert alert-success mb-0">Nhà hàng đã xác nhận khoản chuyển tiền cho đơn này.</div>
      <?php elseif ($d['trangthai_tt'] === 'expired' || (int)$d['trangthai'] === 4): ?>
        <div class="alert alert-warning mb-0">Đơn đã hết hạn hoặc bị hủy. Mã QR không còn hiệu lực; vui lòng không chuyển tiền.</div>
      <?php else: ?>
        <div class="alert alert-secondary mb-0">Đơn hiện không ở trạng thái chờ thanh toán. Vui lòng liên hệ chi nhánh nếu bạn cần hỗ trợ.</div>
      <?php endif; ?>
      <a class="btn btn-outline-secondary mt-3" href="index.php?action=chitietdon&amp;id=<?= (int)$d['id'] ?>">Xem chi tiết đơn</a>
    </div></div>
  </div>
</div>
<?php if ($active): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
  var status = document.getElementById('bankTransferQrStatus');
  if (typeof window.QRCode !== 'function') {
    status.textContent = 'Không tạo được mã QR. Vui lòng nhập thông tin chuyển khoản bên cạnh.';
    return;
  }
  try {
    new window.QRCode(document.getElementById('bankTransferQr'), {
      text: <?= json_encode($qrPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
      width: 240,
      height: 240,
      correctLevel: window.QRCode.CorrectLevel.M
    });
    status.textContent = 'Quét mã bằng ứng dụng ngân hàng.';
  } catch (error) {
    status.textContent = 'Không tạo được mã QR. Vui lòng nhập thông tin chuyển khoản bên cạnh.';
  }
})();
</script>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
