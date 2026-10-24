# Kế hoạch & tài liệu theo giai đoạn - Website GoldenGate

## Giai đoạn 1 - Khởi tạo và khảo sát (Tuần 1)
- Mục tiêu: chốt phạm vi, vai trò, công nghệ. Sản phẩm: bản mô tả dự án, danh sách bên liên quan, rủi ro ban đầu.
- Vai trò hệ thống: Khách hàng, Quản lý chi nhánh, Quản trị tổng.
- Ngoài phạm vi bản 1: thanh toán online, đặt bàn, app di động (xếp vào bản 2).

## Giai đoạn 2 - Phân tích yêu cầu (Tuần 2)
| Mã | Yêu cầu | Vai trò | Ưu tiên |
|---|---|---|---|
| YC01 | Chọn chi nhánh, xem thực đơn theo chi nhánh | Khách | Cao |
| YC02 | Giỏ hàng, đặt món 3 hình thức nhận | Khách | Cao |
| YC03 | Đăng ký/đăng nhập, lịch sử, hủy đơn chờ | Khách | Cao |
| YC04 | Tra cứu đơn không cần tài khoản | Khách | Trung bình |
| YC05 | **Cá nhân hóa: gợi ý món theo lịch sử gọi/xem** | Khách | Cao |
| YC06 | Quản lý chi nhánh, nhóm món, món ăn | Quản trị | Cao |
| YC07 | Tồn kho và thực đơn riêng từng chi nhánh | QT/Quản lý | Cao |
| YC08 | Xử lý đơn: xác nhận → chuẩn bị → hoàn thành / hủy hoàn kho | QT/Quản lý | Cao |
| YC09 | Báo cáo doanh thu, top món, cảnh báo tồn thấp | QT/Quản lý | Trung bình |
| YC10 | Phân quyền, bảo mật | Tất cả | Cao |

Yêu cầu phi chức năng: tải trang dưới 3 giây; không bán vượt tồn kho; mật khẩu băm; chạy trên trình duyệt di động.

## Giai đoạn 3 - Thiết kế (Tuần 3)
- CSDL: 9 bảng (`chinhanh, nhomon, monan, monan_chinhanh, nguoidung, diachi, donhang, donhangct, luotxem_nguoidung`) - xem `data/goldengate.sql`.
- Quyết định thiết kế chính:
  1. Tồn kho nằm ở `monan_chinhanh` (mỗi chi nhánh một số liệu và có thể ngừng phục vụ món riêng).
  2. `donhangct` lưu cả tên món và đơn giá tại thời điểm đặt, đổi giá sau này không làm sai lịch sử.
  3. Khách vãng lai vẫn có hồ sơ (`dadangky=0`); khi đăng ký bằng cùng SĐT, lịch sử được nhận lại → phục vụ cá nhân hóa.
- Kiến trúc: controller (`index.php`) → model (`model/`) → view; một cổng vào cho mỗi khu vực (public, admin).

## Giai đoạn 4 - Lập trình (Tuần 4-6), chia sprint
| Sprint | Nội dung | Kết quả kiểm tra được |
|---|---|---|
| 1 | CSDL, lõi hệ thống, quản trị chi nhánh/nhóm/món/tồn kho | Admin tạo được món, nhập tồn |
| 2 | Thực đơn, giỏ hàng, đặt món (giao dịch + trừ kho) | Đặt món, tồn giảm đúng |
| 3 | Tài khoản, lịch sử, hủy đơn, tra cứu | Khách theo dõi được đơn |
| 4 | **Cá nhân hóa**, báo cáo, phân quyền quản lý chi nhánh | Demo gợi ý và dashboard |

### Chức năng cá nhân hóa (YC05) - mô tả cho báo cáo
- Dữ liệu đầu vào: số phần đã gọi (từ `donhangct`, bỏ đơn hủy), số lần xem (`luotxem_nguoidung`, hoặc cookie với khách chưa đăng nhập).
- "Gọi lại món quen": các món đã gọi nhiều nhất, còn phục vụ và còn hàng tại chi nhánh đang chọn.
- "Có thể bạn sẽ thích": món chưa gọi, điểm = 3 × (lượt xem, tối đa 5, chuẩn hóa) + 2 × (độ thích nhóm món) + 0.5 × (độ phổ biến). Mỗi món kèm lý do hiển thị rõ ràng.
- Khách mới chưa có dữ liệu: hiển thị món bán chạy. Có nút "Đặt lại đơn gần nhất".
- Ưu điểm: đơn giản, giải thích được, không cần dịch vụ ngoài. Hướng mở rộng: gợi ý theo giờ/ngày, lọc cộng tác.

## Giai đoạn 5 - Kiểm thử (Tuần 7)
| TC | Kịch bản | Kết quả mong đợi |
|---|---|---|
| 01 | Chọn chi nhánh B, món bị tắt phục vụ ở B | Không hiện trong thực đơn B |
| 02 | Thêm vượt tồn vào giỏ | Bị giới hạn bằng số tồn |
| 03 | Hai người đặt món chỉ còn 1 phần cùng lúc | Chỉ một đơn thành công |
| 04 | Đặt giao hàng dưới/trên 300.000đ | Phí 15.000đ / miễn phí |
| 05 | Hủy đơn đã xác nhận (quản trị) | Tồn kho được hoàn lại |
| 06 | Quản lý chi nhánh A mở đơn chi nhánh B bằng URL | Bị từ chối |
| 07 | Gửi form thiếu token CSRF | Bị chặn (HTTP 419) |
| 08 | Đăng nhập khach@goldengate.vn | Thấy "Gọi lại món quen" (Bò lúc lắc, Sườn BBQ...) |
| 09 | Khách mới xem vài món rồi tải lại trang chủ | Mục gợi ý thay đổi theo món đã xem |
| 10 | Đặt món bằng SĐT khách vãng lai rồi đăng ký cùng SĐT | Lịch sử đơn được giữ lại |
| 11 | Tải lên file .php đổi đuôi .jpg | Bị từ chối (kiểm tra MIME) |

## Giai đoạn 6 - Triển khai (Tuần 8)
Hosting PHP 7.4+/MySQL, nhập SQL, sửa `config.php`, tắt debug, bật HTTPS, đổi mật khẩu mẫu, thiết lập sao lưu.

## Giai đoạn 7 - Bảo trì và mở rộng
Đặt bàn, thanh toán online (VNPay/MoMo), thông báo email/Zalo, phiếu giảm giá, đánh giá món, màn hình bếp (KDS).

## Rủi ro chính
| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Bán vượt tồn kho | Cao | Khóa dòng trong giao dịch (đã làm) |
| Lộ dữ liệu giữa chi nhánh | Cao | Kiểm tra chi nhánh ở tầng model + controller (đã làm) |
| Trễ tiến độ do mở rộng phạm vi | TB | Chốt phạm vi bản 1, đưa phần còn lại vào bản 2 |
| Dữ liệu cá nhân hóa ít khi mới chạy | TB | Fallback món bán chạy (đã làm) |
