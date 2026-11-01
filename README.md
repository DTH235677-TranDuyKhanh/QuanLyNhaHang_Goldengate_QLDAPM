# GoldenGate - Website quản lý chuỗi nhà hàng

PHP thuần (PDO) + MySQL + Bootstrap 5. Phát triển từ nền bài thực hành CNW PHP_BTTH08.

## Cài đặt (XAMPP / Laragon)
1. Yêu cầu: **PHP 7.4 trở lên** (khuyến nghị 8.x), MySQL/MariaDB, bật extension `pdo_mysql` và `fileinfo`.
2. Copy thư mục `goldengate` vào `htdocs` (XAMPP) hoặc `www` (Laragon).
3. Mở phpMyAdmin → tab **Nhập (Import)** → chọn `database/goldengate.sql` → Thực hiện.
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
đặt bàn chờ nhà hàng xác nhận · nhập voucher giảm giá tại thanh toán ·
**Cá nhân hóa: "Gọi lại món quen", "Có thể bạn sẽ thích", "Đặt lại đơn gần nhất"**.

**Quản trị tổng**: chi nhánh · nhóm món · món ăn (có ảnh) · tồn kho & thực đơn riêng từng chi nhánh · đơn hàng toàn chuỗi ·
người dùng/nhân sự · voucher (giới hạn theo thời hạn, giá trị đơn, lượt dùng, chi nhánh) · báo cáo doanh thu theo chi nhánh, ngày, top món.
**Quản lý chi nhánh**: chỉ thấy và xử lý đơn, yêu cầu đặt bàn, tồn kho, báo cáo của chi nhánh mình.

## Cập nhật cơ sở dữ liệu hiện có
Nếu đã cài bản trước, hãy sao lưu CSDL rồi chạy **một lần** file `database/migrations/202606_add_bookings_and_vouchers.sql` trong phpMyAdmin/CLI.
Không import lại `database/goldengate.sql` trên CSDL đang sử dụng vì file khởi tạo này tạo mới CSDL và xóa CSDL cùng tên trước đó.

## Cấu trúc
```
config/   cấu hình ứng dụng và kết nối cơ sở dữ liệu
core/     bootstrap và hàm dùng chung (CSRF, flash, upload)
model/    truy vấn dữ liệu và logic nghiệp vụ
public/   website khách hàng
admin/    trang quản trị
api/      các endpoint API dùng chung cơ sở dữ liệu chính
database/ CSDL chính (goldengate.sql) và migration (migrations/)
uploads/  ảnh tải lên (món ăn, slider)
docs/     tài liệu dự án
```

## Bảo mật đã áp dụng
Prepared statement toàn bộ · mật khẩu `password_hash` · token CSRF mọi form POST · escape đầu ra `e()` · khóa đăng nhập 5 phút khi sai 5 lần ·
upload ảnh kiểm tra MIME thật, giới hạn 2MB, chặn chạy PHP trong `uploads/` · phân quyền theo vai trò và theo chi nhánh ·
đặt món chạy trong giao dịch có khóa dòng tồn kho (không bán quá số lượng khi nhiều người đặt cùng lúc).

## Trước khi triển khai thật
Đặt `debug => false`, đổi mật khẩu MySQL và 3 tài khoản mẫu, bật HTTPS, tạo user MySQL riêng quyền hạn chế, sao lưu CSDL định kỳ.
