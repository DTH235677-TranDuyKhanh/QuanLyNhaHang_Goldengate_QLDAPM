# GoldenGate — Website quản lý chuỗi nhà hàng

GoldenGate là ứng dụng web quản lý hoạt động bán món ăn và vận hành nhiều chi nhánh nhà hàng. Khách hàng có thể xem thực đơn, đặt món, theo dõi đơn và gửi yêu cầu đặt bàn; nhân viên quản trị xử lý đơn hàng, tồn kho, chi nhánh và báo cáo.

## Mục lục

- [Công nghệ](#công-nghệ)
- [Chức năng](#chức-năng)
- [3.0 Thiết kế hệ thống](#30-thiết-kế-hệ-thống)
- [4.0 Cài đặt và phát triển hệ thống](#40-cài-đặt-và-phát-triển-hệ-thống)
- [Vai trò và quyền hạn](#vai-trò-và-quyền-hạn)
- [Cài đặt trên máy local](#cài-đặt-trên-máy-local)
- [Tài khoản demo](#tài-khoản-demo)
- [Cấu hình](#cấu-hình)
- [Cấu trúc dự án](#cấu-trúc-dự-án)
- [Kiến trúc và luồng hoạt động](#kiến-trúc-và-luồng-hoạt-động)
- [Cơ sở dữ liệu](#cơ-sở-dữ-liệu)
- [API](#api)
- [Bảo mật và lưu ý triển khai](#bảo-mật-và-lưu-ý-triển-khai)
- [Tài liệu liên quan](#tài-liệu-liên-quan)

## Công nghệ

- PHP thuần, tổ chức theo mô hình controller–model–view; PHP 7.4 trở lên (khuyến nghị PHP 8.x).
- MySQL hoặc MariaDB, truy cập qua PDO và extension `pdo_mysql`.
- Bootstrap 5.3.3 qua CDN, CSS riêng cho website khách hàng và trang quản trị.
- JavaScript thuần cho một số tương tác giao diện.
- XAMPP hoặc Laragon dùng để chạy trên máy local.

Không có bước cài thư viện PHP bằng Composer trong dự án hiện tại.

## Chức năng

### Website khách hàng

- Xem thực đơn, lọc theo nhóm món, tìm kiếm món và xem chi tiết món.
- Chọn chi nhánh; thực đơn, tình trạng phục vụ và tồn kho được áp dụng theo chi nhánh.
- Thêm, cập nhật hoặc xóa món trong giỏ hàng; số lượng được giới hạn theo tồn kho.
- Đặt món tại quán, mang đi hoặc giao hàng. Phí giao hàng và ngưỡng miễn phí được cấu hình trong ứng dụng.
- Áp dụng voucher tại bước thanh toán. Voucher có thể bị giới hạn bởi thời gian, giá trị đơn tối thiểu, số lượt dùng và chi nhánh.
- Đặt món với tài khoản hoặc dưới dạng khách vãng lai; đăng ký, đăng nhập, xem lịch sử, đặt lại đơn cũ và hủy đơn đang chờ xác nhận.
- Cập nhật hồ sơ và quản lý nhiều địa chỉ giao hàng, chọn địa chỉ mặc định.
- Tra cứu đơn hàng bằng mã đơn và số điện thoại.
- Chọn thanh toán khi nhận món hoặc thanh toán VNPay; giao dịch trực tuyến chỉ được xác nhận bởi IPN có chữ ký hợp lệ.
- Gửi yêu cầu đặt bàn, xem hoặc hủy yêu cầu đang chờ xác nhận nếu đã đăng nhập. Số chỗ được kiểm tra khi nhà hàng xác nhận theo sức chứa và thời lượng cấu hình từng chi nhánh.
- Xem sự kiện và ưu đãi đang diễn ra theo chi nhánh; nội dung có thể hiển thị mã voucher liên quan.
- Gửi nhận xét và đánh giá 1–5 sao cho món trong đơn đã hoàn thành; xem điểm trung bình, phản hồi của nhà hàng và các đánh giá đang được hiển thị.
- Cá nhân hóa nội dung: món đã gọi nhiều, gợi ý dựa trên lịch sử xem/đặt và đặt lại đơn gần nhất.

### Trang quản trị

- Bảng tổng quan doanh thu, đơn hàng, món bán chạy và cảnh báo món sắp hết.
- Quản lý đơn hàng: xem lịch sử trạng thái, cập nhật tiến độ và ghi nhận thanh toán tiền mặt; đơn VNPay chỉ được xử lý sau IPN xác thực.
- Quản lý yêu cầu đặt bàn và trạng thái xử lý.
- Quản lý sức chứa/thời lượng đặt bàn theo chi nhánh, kiểm tra tổng số khách bị chồng lấn trước khi xác nhận.
- Quản lý tồn kho và bật/tắt phục vụ món theo từng chi nhánh; lưu sổ biến động nhập, điều chỉnh, bán và hoàn kho.
- Quản lý voucher, sự kiện/ưu đãi, phản hồi/đánh giá, chi nhánh, nhóm món, món ăn, ảnh slider và tài khoản nhân sự.
- Xem báo cáo doanh thu theo ngày, chi nhánh và món bán chạy.
- Quản lý chi nhánh chỉ truy cập dữ liệu chi nhánh được phân công; quản trị tổng có quyền trên toàn hệ thống.

Trạng thái đơn hàng: **Chờ xác nhận → Đã xác nhận → Đang chuẩn bị → Hoàn thành**; đơn có thể chuyển sang **Đã hủy** theo quy tắc xử lý của hệ thống.

## 3.0 Thiết kế hệ thống

### 3.1 Thiết kế kiến trúc tổng thể

Ứng dụng dùng kiến trúc PHP theo mô hình **controller → model → view**, không cần framework:

```text
Trình duyệt
  ├── public/index.php  (khu vực khách hàng)
  └── admin/index.php   (khu vực quản trị)
           │
           ├── core/bootstrap.php + core/helpers.php
           ├── model/*.php ── model/db.php (PDO)
           ├── MySQL/MariaDB
           └── VNPay (chỉ khi bật thanh toán trực tuyến)
```

Hai controller dùng chung session, cấu hình, helper và model. Model phụ trách truy vấn/nghiệp vụ; các file PHP trong `public/` và `admin/` dựng giao diện. Các biểu mẫu POST làm thay đổi dữ liệu đều kiểm tra token CSRF. Quyền quản trị và giới hạn chi nhánh được kiểm tra ở controller/model. Đặt/hủy đơn, thanh toán và cập nhật tồn kho chạy trong giao dịch CSDL. VNPay được xác thực chữ ký HMAC-SHA512 và chỉ IPN máy chủ mới ghi nhận thanh toán.

### 3.2 Thiết kế giao diện người dùng

- **Website khách hàng:** điều hướng thực đơn/nhóm món/tìm kiếm, chọn chi nhánh, giỏ hàng, đăng nhập/đăng ký, đặt bàn, sự kiện & ưu đãi; có trang chủ, chi tiết món, giỏ hàng/thanh toán, đơn hàng, tra cứu và đánh giá.
- **Trang quản trị:** sidebar điều hướng, dashboard, danh sách và biểu mẫu quản lý; gồm đơn hàng, đặt bàn, tồn kho, báo cáo, voucher, sự kiện, đánh giá và dữ liệu danh mục.
- **Thành phần giao diện:** Bootstrap 5.3.3, CSS riêng; bố cục co giãn theo kích thước màn hình. Form có nhãn, kiểm tra đầu vào phía trình duyệt và thông báo kết quả.
- **Luồng đánh giá:** chỉ hiển thị biểu mẫu đánh giá trong chi tiết đơn đã hoàn thành; đánh giá công khai trên trang chi tiết món sau khi được bật.
- **Luồng sự kiện:** khách xem các sự kiện đang trong thời hạn, đang bật và áp dụng toàn hệ thống hoặc chi nhánh hiện chọn.

### 3.3 Thiết kế cơ sở dữ liệu

CSDL quan hệ InnoDB gồm 17 bảng nghiệp vụ. Các quan hệ chính:

- `chinhanh` 1–N `donhang`, `datban`; `chinhanh` N–N `monan` qua `monan_chinhanh` để lưu tồn kho và trạng thái phục vụ theo cơ sở.
- `nhomon` 1–N `monan`; `monan` 1–N `donhangct`.
- `nguoidung` 1–N `donhang`, `datban`, `danhgia`; `donhang` 1–N `donhangct`.
- `voucher` có thể gắn với nhiều đơn; `sukien` và voucher có thể áp dụng toàn hệ thống hoặc một chi nhánh.
- `danhgia` tham chiếu người dùng, đơn hàng và món; khóa duy nhất `(donhang_id, monan_id)` ngăn đánh giá trùng một món trong cùng đơn.
- `sukien` liên kết trực tiếp đến `voucher`; mã ưu đãi không còn là văn bản tự do.
- `donhang_lichsu`, `datban_lichsu`, `tonkho_lichsu` ghi lại người thao tác, thời điểm và biến động để phục vụ đối soát vận hành.
- `donhang` lưu riêng phương thức/trạng thái thanh toán và mã tham chiếu VNPay, không đồng nhất trạng thái thanh toán với trạng thái chế biến.

Giá và tên món được chụp vào `donhangct` tại thời điểm đặt. Điểm đánh giá được kiểm tra với đơn đã hoàn thành ở tầng nghiệp vụ; khóa duy nhất trong CSDL là lớp bảo vệ bổ sung.

## 4.0 Cài đặt và phát triển hệ thống

| Mục | Nội dung trong dự án | Tình trạng |
|---|---|---|
| 4.1 Xây dựng cơ sở dữ liệu | Schema và dữ liệu mẫu trong `database/goldengate.sql`; migration cho hệ thống đã cài | Hoàn thành |
| 4.2 Giao diện trang chủ và quản trị | Website khách hàng và dashboard/quản trị Bootstrap | Hoàn thành |
| 4.3 Quản lý người dùng | Đăng ký/đăng nhập, phân quyền, quản lý nhân sự, hồ sơ và địa chỉ khách | Hoàn thành |
| 4.4 Quản lý nhà hàng và thực đơn | Chi nhánh, nhóm món, món, tồn kho, trạng thái phục vụ và sổ biến động | Hoàn thành |
| 4.5 Đặt bàn trực tiếp | Sức chứa/thời lượng theo chi nhánh, kiểm tra chồng lấn khi xác nhận, lưu lịch sử | Hoàn thành |
| 4.6 Sự kiện và khuyến mãi | Sự kiện liên kết voucher hợp lệ theo thời hạn/chi nhánh | Hoàn thành |
| 4.7 Tìm kiếm và lọc sản phẩm | Tìm tên món, lọc theo nhóm món và chi nhánh | Hoàn thành |
| 4.8 Phản hồi và đánh giá | Đánh giá đơn hoàn thành; quản trị phản hồi và ẩn/hiện đánh giá | Hoàn thành |
| 4.9 Tích hợp hệ thống | Lịch sử đơn/tồn kho, thanh toán COD/VNPay, IPN xác thực, session/CSRF/quyền truy cập | Cần cấu hình merchant và HTTPS để chạy giao dịch thật |

## Vai trò và quyền hạn

| Vai trò | Mã vai trò | Quyền chính |
|---|---:|---|
| Quản trị tổng | 1 | Quản lý toàn hệ thống, bao gồm chi nhánh, nhân sự, món ăn, nhóm món, slider và voucher |
| Quản lý chi nhánh | 2 | Xem và xử lý đơn, đặt bàn, tồn kho và báo cáo của chi nhánh được gán |
| Khách hàng | 3 | Đặt món, quản lý đơn cá nhân và gửi yêu cầu đặt bàn |

## Cài đặt trên máy local

1. Cài XAMPP hoặc Laragon, bật Apache và MySQL.
2. Đặt thư mục dự án trong thư mục web server, ví dụ:
   - XAMPP: `C:\xampp\htdocs\QuanLyNhaHang`
   - Laragon: thư mục `www` tương ứng.
3. Mở phpMyAdmin, chọn **Import**, nhập file `database/goldengate.sql`.
   - File này tạo CSDL `goldengate` và dữ liệu mẫu.
   - **Lưu ý:** file có lệnh xóa CSDL `goldengate` trước khi tạo lại. Chỉ dùng để cài mới hoặc sau khi đã sao lưu; không import lên CSDL đang sử dụng.
4. Kiểm tra thông tin kết nối trong `config/config.php` và bật extension PHP `pdo_mysql`, `fileinfo`.
5. Khi nâng cấp cơ sở dữ liệu cũ, sao lưu trước và áp dụng lần lượt các migration chưa chạy; không import lại file cài mới lên dữ liệu đang dùng.
6. Mở website. Với tên thư mục ví dụ ở trên:
   - Website: `http://localhost/QuanLyNhaHang/`
   - Quản trị: `http://localhost/QuanLyNhaHang/admin/`

Nếu đổi tên thư mục dự án, thay `QuanLyNhaHang` trong URL bằng tên thư mục mới. Bootstrap được tải qua CDN nên trình duyệt cần truy cập Internet để tải stylesheet và script của Bootstrap.

## Tài khoản demo

Các tài khoản mẫu được cung cấp cho môi trường demo sau khi nhập SQL:

| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Quản trị tổng | `admin@goldengate.vn` | `Admin@123` |
| Quản lý chi nhánh Quận 1 | `quanly1@goldengate.vn` | `Quanly@123` |
| Khách hàng có lịch sử mẫu | `khach@goldengate.vn` | `Khach@123` |

Không sử dụng các mật khẩu mẫu trong môi trường triển khai thật.

## Cấu hình

Sửa các giá trị trong `config/config.php`:

| Khóa | Ý nghĩa | Giá trị mặc định |
|---|---|---|
| `db.host`, `db.port` | Máy chủ và cổng MySQL | `localhost`, `3306` |
| `db.name` | Tên CSDL | `goldengate` |
| `db.user`, `db.pass` | Tài khoản MySQL | `root`, mật khẩu rỗng (mặc định XAMPP) |
| `site_name` | Tên hiển thị của website | `GoldenGate` |
| `debug` | Hiển thị chi tiết ngoại lệ trong môi trường phát triển | `true` |
| `free_ship_from` | Giá trị đơn được miễn phí giao hàng | `300000` |
| `ship_fee` | Phí giao hàng | `15000` |
| `VNPAY_TMN_CODE` | Mã merchant từ VNPay (environment) | Chưa cấu hình |
| `VNPAY_HASH_SECRET` | Khóa ký HMAC (environment, giữ bí mật) | Chưa cấu hình |
| `VNPAY_URL` | URL gateway HTTPS của VNPay hoặc sandbox | Chưa cấu hình |
| `VNPAY_RETURN_URL` | URL HTTPS trả khách về ứng dụng | Chưa cấu hình |
| `VNPAY_IPN_URL` | URL HTTPS IPN công khai | Chưa cấu hình |

Tiền tệ được hiển thị bằng đồng Việt Nam; múi giờ ứng dụng là `Asia/Ho_Chi_Minh`.

### Cấu hình VNPay

Thông tin merchant phải được đặt trong environment của PHP/web server, không ghi vào source code, CSDL hoặc README:

| Biến môi trường | Mô tả |
|---|---|
| `VNPAY_TMN_CODE` | Mã website/merchant do VNPay cấp |
| `VNPAY_HASH_SECRET` | Secret dùng tạo và xác thực chữ ký |
| `VNPAY_URL` | Gateway chính thức hoặc sandbox |
| `VNPAY_RETURN_URL` | URL HTTPS kết thúc bằng `index.php?action=vnpayreturn` |
| `VNPAY_IPN_URL` | URL HTTPS trỏ đến `public/vnpay_ipn.php` |

Ứng dụng chỉ chấp nhận gateway HTTPS chính thức `https://pay.vnpay.vn/vpcpay.html` hoặc sandbox `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`. Giao dịch thật cần merchant credentials, website HTTPS hoạt động công khai và IPN URL được cấu hình/đăng ký với VNPay. Không nhập secret qua chat. Đơn thanh toán thất bại/hết hạn tự giải phóng tồn kho khi IPN nhận kết quả hoặc khi tác vụ hết hạn chạy. Cấu hình Windows Task Scheduler/cron chạy `php bin/expire_vnpay.php` mỗi phút để giải phóng đơn quá hạn nếu khách không quay lại; giao dịch thành công đến muộn được đưa vào đối soát thủ công.

Tham khảo [tài liệu thanh toán PAY chính thức của VNPay](https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html) khi đăng ký endpoint và kiểm thử với merchant.

## Cấu trúc dự án

```text
QuanLyNhaHang/
├── index.php                 # Chuyển yêu cầu gốc tới website khách hàng
├── README.md
├── config/
│   ├── config.php            # Cấu hình CSDL và ứng dụng
│   └── .htaccess
├── core/
│   ├── bootstrap.php         # Khởi tạo session, cấu hình, autoload, xử lý lỗi
│   ├── helpers.php           # CSRF, phiên đăng nhập, tiện ích, upload ảnh
│   └── .htaccess
├── model/                    # Truy vấn CSDL và nghiệp vụ, gồm vnpay.php
├── public/                   # Website khách hàng (controller và view)
│   ├── index.php
│   ├── vnpay_ipn.php         # Điểm nhận IPN có xác thực chữ ký
│   ├── inc/
│   └── assets/gg.css
├── admin/                    # Website quản trị
│   ├── index.php
│   ├── inc/
│   └── admin.css
├── api/
│   └── get_foods.php         # Endpoint JSON danh sách món
├── database/
│   ├── goldengate.sql        # CSDL và dữ liệu cài mới
│   └── migrations/
│       ├── 202606_add_bookings_and_vouchers.sql
│       ├── 202611_add_events_and_reviews.sql
│       └── 202611_operational_workflows.sql
├── bin/
│   └── expire_vnpay.php      # Tác vụ giải phóng đơn VNPay quá hạn (CLI)
├── docs/
│   └── GIAI_DOAN.md          # Yêu cầu, giai đoạn và kịch bản kiểm thử
├── hinhanhmonan/             # Ảnh món ăn mẫu
└── uploads/
    ├── monan/                # Ảnh món tải lên
    ├── slider/               # Ảnh slider tải lên
    └── sukien/               # Ảnh sự kiện tải lên
```

Các lớp model gồm chi nhánh, đặt bàn, đánh giá, đơn hàng, giỏ hàng, gợi ý, món ăn, người dùng, nhóm món, sự kiện, slider, thống kê và voucher. Tên lớp được autoload từ file PHP tương ứng trong `model/`.

## Kiến trúc và luồng hoạt động

1. `index.php` ở thư mục gốc chuyển tiếp tới website trong `public/`.
2. `public/index.php` và `admin/index.php` là controller chính của từng khu vực; hành động được chọn qua tham số `action`.
3. Hai khu vực cùng khởi tạo `core/bootstrap.php`, nạp cấu hình, session, helper và các lớp model.
4. Model dùng lớp `DB` trong `model/db.php` để giao tiếp với MySQL qua PDO.
5. Controller gọi model rồi nạp các file giao diện PHP trong `public/` hoặc `admin/`.
6. Các thao tác ghi dữ liệu qua POST đều kiểm tra token CSRF. Dữ liệu nhập được xử lý ở controller/model và nội dung hiển thị được escape bằng helper `e()`.

Khi đặt món, hệ thống kiểm tra lại tình trạng phục vụ và số lượng tồn tại chi nhánh, sau đó tạo đơn và cập nhật tồn kho trong giao dịch CSDL để tránh bán vượt số lượng khi có nhiều yêu cầu đồng thời. Chi tiết món lưu tên và đơn giá tại thời điểm đặt để bảo toàn lịch sử khi thông tin món thay đổi.

Đơn VNPay giữ tồn kho trong thời hạn giao dịch. IPN hợp lệ xác nhận thanh toán và chuyển đơn sang chờ nhà hàng xử lý; IPN thất bại/hết hạn giải phóng tồn kho và lượt voucher trong một giao dịch. Thanh toán thành công đến sau khi đơn đã hủy/hết hạn chuyển sang `manual_review` và không tự động đưa đơn vào bếp. Hủy đơn đã thu tiền bị chặn để tránh hoàn kho mà chưa xử lý hoàn tiền.

## Cơ sở dữ liệu

File cài mới `database/goldengate.sql` tạo CSDL `goldengate`, các bảng và dữ liệu mẫu. Các bảng nghiệp vụ chính:

| Bảng | Nội dung |
|---|---|
| `chinhanh` | Thông tin, giờ hoạt động, trạng thái và sức chứa/thời lượng đặt bàn |
| `nhomon` | Nhóm/danh mục món ăn |
| `monan` | Thông tin, giá, ảnh và trạng thái món |
| `monan_chinhanh` | Tồn kho và trạng thái phục vụ của món theo chi nhánh |
| `slider` | Nội dung slider trang chủ |
| `nguoidung` | Tài khoản khách hàng và nhân sự, vai trò, chi nhánh |
| `diachi` | Địa chỉ lưu cho người dùng |
| `donhang`, `donhangct` | Đơn hàng và các dòng món tại thời điểm đặt |
| `donhang_lichsu` | Lịch sử trạng thái và ghi chú nghiệp vụ của đơn |
| `datban` | Yêu cầu đặt bàn và trạng thái xử lý |
| `datban_lichsu` | Lịch sử xử lý yêu cầu đặt bàn |
| `tonkho_lichsu` | Biến động tồn kho theo món/chi nhánh/đơn tham chiếu |
| `voucher` | Mã giảm giá, điều kiện, thời hạn và số lượt sử dụng |
| `sukien` | Nội dung sự kiện/ưu đãi, voucher liên kết, ảnh, thời hạn và chi nhánh áp dụng |
| `danhgia` | Số sao và nhận xét của khách cho món đã mua trong đơn hoàn thành |
| `donhang_lichsu`, `datban_lichsu` | Lịch sử trạng thái và người thao tác |
| `tonkho_lichsu` | Nhật ký nhập, điều chỉnh, bán và hoàn kho |
| `luotxem_nguoidung` | Lịch sử xem món dùng cho gợi ý cá nhân hóa |

### Cập nhật CSDL đã cài từ phiên bản cũ

Sao lưu CSDL trước khi cập nhật. Nếu CSDL chưa có đặt bàn/voucher, chạy `database/migrations/202606_add_bookings_and_vouchers.sql` trước. Sau đó chạy `database/migrations/202611_add_events_and_reviews.sql` (nếu chưa chạy), rồi chạy một lần `database/migrations/202611_operational_workflows.sql`. Migration vận hành bổ sung trạng thái thanh toán, sức chứa đặt bàn, liên kết voucher, phản hồi đánh giá và các bảng lịch sử. Không chạy lại migration đã áp dụng vì các lệnh không được thiết kế để chạy lặp lại. Cài mới thì chỉ cần nhập `database/goldengate.sql`.

Quy trình này lưu lịch sử mới phát sinh sau khi migration được áp dụng; các bản ghi đơn/đặt bàn/tồn kho cũ không được dựng lại lịch sử từ dữ liệu suy đoán.

## API

`api/get_foods.php` trả về JSON danh sách món đang hoạt động cùng tên, giá, trạng thái, ảnh và tên nhóm món.

Ví dụ phản hồi thành công:

```json
{
  "status": "success",
  "data": [
    {
      "food_id": 1,
      "food_name": "Gỏi cuốn tôm thịt",
      "price": "55000",
      "status": "available",
      "image": null,
      "category_name": "Khai vị"
    }
  ]
}
```

## Bảo mật và lưu ý triển khai

- Truy vấn CSDL dùng prepared statements qua PDO.
- Mật khẩu tài khoản được lưu dưới dạng hash; không lưu mật khẩu rõ trong CSDL.
- Form POST kiểm tra CSRF; dữ liệu hiển thị được escape HTML.
- Có kiểm tra quyền quản trị và ràng buộc quản lý theo chi nhánh.
- Đăng nhập có cơ chế khóa tạm thời sau nhiều lần nhập sai.
- VNPay kiểm tra chữ ký HMAC-SHA512, số tiền, mã merchant và trạng thái giao dịch; Return URL chỉ hiển thị kết quả, IPN mới cập nhật thanh toán. Giao dịch đến muộn được đánh dấu đối soát, không tự động xác nhận đơn.
- Ảnh tải lên giới hạn 2 MB, kiểm tra MIME thực và chỉ nhận JPG, PNG hoặc WEBP; thư mục upload có cấu hình hạn chế thực thi PHP.
- Trước khi đưa lên máy chủ thật:
  1. Đặt `debug` thành `false`.
  2. Đổi mật khẩu tài khoản demo và không dùng tài khoản MySQL `root`; tạo tài khoản riêng với quyền tối thiểu.
  3. Bật HTTPS, giới hạn quyền ghi thư mục `uploads/` và sao lưu CSDL định kỳ.
  4. Đảm bảo cấu hình máy chủ không công khai các file cấu hình hoặc dữ liệu nội bộ.
  5. Thiết lập các biến môi trường VNPay và IPN/Task Scheduler trước khi bật nhận thanh toán trực tuyến.

## Tài liệu liên quan

- [Kế hoạch, yêu cầu và kiểm thử theo giai đoạn](docs/GIAI_DOAN.md)
- [Cấu hình ứng dụng](config/config.php)
- [Schema và dữ liệu khởi tạo](database/goldengate.sql)
- [Migration đặt bàn và voucher](database/migrations/202606_add_bookings_and_vouchers.sql)
- [Migration sự kiện và đánh giá](database/migrations/202611_add_events_and_reviews.sql)
- [Migration quy trình vận hành và thanh toán](database/migrations/202611_operational_workflows.sql)
