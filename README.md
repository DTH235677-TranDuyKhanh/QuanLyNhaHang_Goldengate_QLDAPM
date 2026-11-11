# GoldenGate — Quản lý nhà hàng

Website PHP quản lý chuỗi nhà hàng, hỗ trợ khách đặt món/đặt bàn và nhân viên quản lý đơn, thực đơn, tồn kho, khuyến mãi, đánh giá và báo cáo.

## Chức năng chính

- **Khách hàng:** xem thực đơn theo chi nhánh, giỏ hàng, voucher, đặt món, theo dõi/hủy đơn, đặt bàn, đánh giá và nhận gợi ý món cá nhân hóa.
- **Thanh toán:** tiền mặt khi nhận món hoặc chuyển khoản Vietcombank bằng QR; nhân viên xác nhận chuyển khoản sau khi đối chiếu sao kê.
- **Quản trị:** quản lý đơn, chi nhánh, món, tồn kho, đặt bàn, voucher, sự kiện, đánh giá, tài khoản và báo cáo.

## Công nghệ

- PHP 7.4 trở lên (khuyến nghị PHP 8.x), MySQL/MariaDB và PDO.
- Bootstrap 5, CSS và JavaScript.
- Môi trường phát triển đề xuất: XAMPP hoặc Laragon.

## Cài đặt

1. Cài XAMPP/Laragon, bật Apache và MySQL, đặt dự án trong thư mục web server.
2. Tạo database rỗng tên `goldengate` (hoặc dùng user MySQL có quyền tạo database).
3. Import **`database/goldengate.sql`** bằng phpMyAdmin. Đây là file SQL duy nhất, chứa schema và dữ liệu demo.
   - Chỉ import vào database mới hoặc rỗng. Không dùng để nâng cấp database đang có.
4. Kiểm tra cấu hình kết nối MySQL trong `config/config.php`; bật PHP extensions `pdo_mysql` và `fileinfo`.
5. Mở website tại `http://localhost/QuanLyNhaHang/`; trang quản trị tại `http://localhost/QuanLyNhaHang/admin/`.

> Sao lưu dữ liệu trước mọi thao tác thay đổi database. File SQL cài đặt không xóa database hiện có nhưng sẽ lỗi nếu các bảng đã tồn tại.

## Tài khoản demo

| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Quản trị | `admin@goldengate.vn` | `Admin@123` |
| Quản lý chi nhánh | `quanly1@goldengate.vn` | `Quanly@123` |
| Khách hàng | `khach@goldengate.vn` | `Khach@123` |

Chỉ dùng tài khoản mẫu trong môi trường demo. Đổi mật khẩu trước khi triển khai thật.

## Cấu hình thanh toán

Thông tin tài khoản nhận chuyển khoản và thời hạn thanh toán nằm trong `config/config.php`. QR VietQR được tạo trong trình duyệt; cần Internet để tải thư viện QR từ CDN. Đơn chuyển khoản giữ tồn kho 15 phút. Có thể chạy tác vụ sau mỗi phút bằng Windows Task Scheduler hoặc cron:

```text
php bin/expire_bank_transfers.php
```

Nhân viên cần kiểm tra sao kê, số tiền và nội dung chuyển khoản trước khi xác nhận thanh toán trong trang chi tiết đơn.

## Cấu trúc dự án

```text
admin/       Trang quản trị
api/         Endpoint JSON
bin/         Tác vụ CLI
config/      Cấu hình ứng dụng
core/        Bootstrap và helper dùng chung
database/    goldengate.sql (schema và dữ liệu demo)
docs/        Tài liệu yêu cầu và kiểm thử
model/       Nghiệp vụ và truy vấn dữ liệu
public/      Website khách hàng và giao diện
uploads/     Ảnh tải lên
hinhanhmonan/ Ảnh món mẫu
```

Ứng dụng dùng `public/index.php` và `admin/index.php` làm controller; model truy cập database qua PDO. Các hành động ghi dữ liệu qua POST dùng CSRF; quyền quản trị và phạm vi chi nhánh được kiểm tra ở tầng ứng dụng.

## API

`api/get_foods.php` trả danh sách món ăn dạng JSON.
