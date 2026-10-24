# GoldenGate - Website quản lý chuỗi nhà hàng

PHP thuần (PDO) + MySQL + Bootstrap 5. Phát triển từ nền bài thực hành CNW PHP_BTTH08.

## Cài đặt (XAMPP / Laragon)
1. Yêu cầu: **PHP 7.4 trở lên** (khuyến nghị 8.x), MySQL/MariaDB, bật extension `pdo_mysql` và `fileinfo`.
2. Copy thư mục `goldengate` vào `htdocs` (XAMPP) hoặc `www` (Laragon).
3. Mở phpMyAdmin → tab **Nhập (Import)** → chọn `data/goldengate.sql` → Thực hiện.
   (File tự tạo CSDL `goldengate` cùng dữ liệu mẫu.)
4. Sửa `config/config.php` nếu user/mật khẩu MySQL khác mặc định.
5. Mở trình duyệt: `http://localhost/goldengate/`

## Tài khoản mẫu
| Vai trò | Đăng nhập | Mật khẩu |
|---|---|---|
| Quản trị tổng | admin@goldengate.vn | Admin@123 |
| Quản lý chi nhánh Quận 1 | quanly1@goldengate.vn | Quanly@123 |
| Khách hàng (có lịch sử để demo gợi ý) | khach@goldengate.vn | Khach@123 |

Trang quản trị: `http://localhost/goldengate/admin/`

## Chức năng
**Khách hàng**: chọn chi nhánh · xem thực đơn theo nhóm / tìm kiếm · giỏ hàng · đặt món (tại quán / mang đi / giao hàng, có phí giao) ·
đặt không cần tài khoản · đăng ký / đăng nhập · lịch sử đơn · hủy đơn chờ xác nhận · tra cứu đơn bằng mã + SĐT ·
**Cá nhân hóa: "Gọi lại món quen", "Có thể bạn sẽ thích", "Đặt lại đơn gần nhất"**.

**Quản trị tổng**: chi nhánh · nhóm món · món ăn (có ảnh) · tồn kho & thực đơn riêng từng chi nhánh · đơn hàng toàn chuỗi ·
người dùng/nhân sự · báo cáo doanh thu theo chi nhánh, ngày, top món.
**Quản lý chi nhánh**: chỉ thấy và xử lý đơn, tồn kho, báo cáo của chi nhánh mình.

## Cấu trúc
```
config/   cấu hình (DB, phí giao)         core/    bootstrap + hàm tiện ích (CSRF, flash, upload)
model/    DB, MonAn, DonHang, GoiY...     public/  website khách hàng (index.php là controller)
admin/    trang quản trị                  data/    goldengate.sql
uploads/  ảnh món ăn                      docs/    tài liệu giai đoạn dự án
```

## Bảo mật đã áp dụng
Prepared statement toàn bộ · mật khẩu `password_hash` · token CSRF mọi form POST · escape đầu ra `e()` · khóa đăng nhập 5 phút khi sai 5 lần ·
upload ảnh kiểm tra MIME thật, giới hạn 2MB, chặn chạy PHP trong `uploads/` · phân quyền theo vai trò và theo chi nhánh ·
đặt món chạy trong giao dịch có khóa dòng tồn kho (không bán quá số lượng khi nhiều người đặt cùng lúc).

## Trước khi triển khai thật
Đặt `debug => false`, đổi mật khẩu MySQL và 3 tài khoản mẫu, bật HTTPS, tạo user MySQL riêng quyền hạn chế, sao lưu CSDL định kỳ.
