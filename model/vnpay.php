<?php
class VnPay {
    private static function config() {
        return cfg('vnpay') ?: [];
    }

    public static function daCauHinhDayDu() {
        $config = self::config();
        foreach (['tmn_code', 'hash_secret', 'url', 'return_url', 'ipn_url'] as $key) {
            if (empty($config[$key])) return false;
        }
        foreach (['url', 'return_url', 'ipn_url'] as $key) {
            $parts = parse_url((string)$config[$key]);
            if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) return false;
        }
        $gateway = parse_url((string)$config['url']);
        $allowedGateways = [
            'pay.vnpay.vn/vpcpay.html',
            'sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        ];
        $gatewayAddress = strtolower(($gateway['host'] ?? '') . ($gateway['path'] ?? ''));
        if (!in_array($gatewayAddress, $allowedGateways, true)) return false;
        return true;
    }

    public static function yeuCauCauHinh() {
        if (!self::daCauHinhDayDu()) {
            throw new RuntimeException('Thanh toán VNPay chưa được cấu hình đầy đủ. Cần thiết lập mã merchant, secret và các URL HTTPS trong biến môi trường.');
        }
    }

    public static function taoUrlThanhToan(array $order, $clientIp) {
        self::yeuCauCauHinh();
        $ip = filter_var((string)$clientIp, FILTER_VALIDATE_IP);
        if (!$ip) throw new RuntimeException('Không xác định được địa chỉ IP hợp lệ để tạo giao dịch VNPay.');
        $amount = (int)$order['tongtien'];
        if ($amount < 1 || $amount > 9999999999) {
            throw new RuntimeException('Số tiền đơn hàng vượt giới hạn giao dịch VNPay.');
        }

        $config = self::config();
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $expires = $now->modify('+' . max(5, min(30, (int)($config['expire_minutes'] ?? 15))) . ' minutes');
        $reference = 'DH' . (int)$order['id'] . $now->format('YmdHis');
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $config['tmn_code'],
            'vnp_Amount' => (string)($amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $reference,
            'vnp_OrderInfo' => 'Thanh toan don hang GG' . str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $config['return_url'],
            'vnp_IpAddr' => $ip,
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_ExpireDate' => $expires->format('YmdHis'),
        ];
        $query = self::queryString($params);
        $signature = hash_hmac('sha512', $query, $config['hash_secret']);
        return [
            'url' => $config['url'] . '?' . $query . '&vnp_SecureHash=' . $signature,
            'reference' => $reference,
            'expires_at' => $expires->format('Y-m-d H:i:s'),
        ];
    }

    public static function xacThucChuKy(array $params) {
        $provided = (string)($params['vnp_SecureHash'] ?? '');
        if ($provided === '') return false;
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
        foreach ($params as $key => $value) {
            if (strpos((string)$key, 'vnp_') !== 0 || is_array($value) || is_object($value)) {
                unset($params[$key]);
            }
        }
        if (!$params) return false;
        $config = self::config();
        if (empty($config['hash_secret'])) return false;
        $expected = hash_hmac('sha512', self::queryString($params), $config['hash_secret']);
        return hash_equals($expected, strtolower($provided));
    }

    public static function thongBaoThuongNhanHopLe(array $params) {
        $config = self::config();
        return isset($params['vnp_TmnCode'], $params['vnp_CurrCode'], $params['vnp_Command'])
            && hash_equals((string)($config['tmn_code'] ?? ''), (string)$params['vnp_TmnCode'])
            && (string)$params['vnp_CurrCode'] === 'VND'
            && (string)$params['vnp_Command'] === 'pay';
    }

    private static function queryString(array $params) {
        ksort($params, SORT_STRING);
        return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
    }
}
