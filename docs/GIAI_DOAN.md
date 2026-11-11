# Kế hoạch & tài liệu theo giai đoạn - Website GoldenGate

## Giai đoạn 1 - Khởi tạo và khảo sát (Tuần 1)
- Mục tiêu: chốt phạm vi, vai trò, công nghệ. Sản phẩm: bản mô tả dự án, danh sách bên liên quan, rủi ro ban đầu.
- Vai trò hệ thống: Khách hàng, Quản lý chi nhánh, Quản trị tổng.
- Ngoài phạm vi: app di động. Thanh toán gồm tiền mặt khi nhận món hoặc chuyển khoản QR, được nhân viên đối chiếu thủ công.

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
| YC11 | Sự kiện/ưu đãi theo thời hạn, chi nhánh; voucher áp dụng khi đặt món | QT/Khách | Trung bình |
| YC12 | Đánh giá món đã mua sau khi đơn hoàn thành | Khách/QT | Trung bình |
| YC13 | Hồ sơ khách hàng, nhiều địa chỉ giao hàng và địa chỉ mặc định | Khách | Trung bình |
| YC14 | Sức chứa/khung thời gian đặt bàn theo chi nhánh, kiểm tra khi xác nhận | QT/Quản lý | Cao |
| YC15 | Lưu lịch sử thay đổi đơn, đặt bàn và biến động tồn kho | QT/Quản lý | Cao |
| YC16 | Thanh toán COD/chuyển khoản QR, xác nhận thủ công và hết hạn đơn | Khách/QT | Cao |
| YC17 | Sự kiện liên kết voucher còn hạn, đúng phạm vi chi nhánh | QT/Khách | Trung bình |

Yêu cầu phi chức năng: tải trang dưới 3 giây; không bán vượt tồn kho/đặt quá sức chứa; mật khẩu băm; callback thanh toán xác thực chữ ký và xử lý lặp; chạy trên trình duyệt di động.

## Giai đoạn 3 - Thiết kế (Tuần 3)
- CSDL: 17 bảng nghiệp vụ trong `database/goldengate.sql`, gồm đặt bàn, voucher, sự kiện, đánh giá, thanh toán và bảng lịch sử vận hành.
- Quyết định thiết kế chính:
  1. Tồn kho hiện tại nằm ở `monan_chinhanh`; `tonkho_lichsu` ghi nhận mọi lần nhập/điều chỉnh/bán/hoàn kho.
  2. `donhangct` lưu cả tên món và đơn giá tại thời điểm đặt, đổi giá sau này không làm sai lịch sử.
  3. Khách vãng lai vẫn có hồ sơ (`dadangky=0`); khi đăng ký bằng cùng SĐT, lịch sử được nhận lại → phục vụ cá nhân hóa.
  4. Chuyển khoản QR có trạng thái riêng trong `donhang`; chỉ nhân viên sau khi đối chiếu sao kê mới xác nhận thanh toán.
- Kiến trúc: controller (`index.php`) → model (`model/`) → view; một cổng vào cho mỗi khu vực (public, admin).

## Giai đoạn 4 - Lập trình (Tuần 4-6), chia sprint
| Sprint | Nội dung | Kết quả kiểm tra được |
|---|---|---|
| 1 | CSDL, lõi hệ thống, quản trị chi nhánh/nhóm/món/tồn kho | Admin tạo được món, nhập tồn |
| 2 | Thực đơn, giỏ hàng, đặt món (giao dịch + trừ kho) | Đặt món, tồn giảm đúng |
| 3 | Tài khoản, lịch sử, hủy đơn, tra cứu | Khách theo dõi được đơn |
| 4 | **Cá nhân hóa**, báo cáo, phân quyền quản lý chi nhánh | Demo gợi ý và dashboard |
| 5 | Sự kiện/voucher, phản hồi đánh giá, hồ sơ/địa chỉ và lịch sử vận hành | Khách nhận phản hồi; nhân viên tra được lịch sử đơn và tồn kho |
| 6 | Chuyển khoản QR, xác nhận thủ công và hết hạn thanh toán | QR có mã đơn/số tiền chính xác; chỉ nhân viên xác nhận sau khi đối chiếu sao kê |

### Chức năng cá nhân hóa (YC05) - mô tả cho báo cáo
- Dữ liệu đầu vào: số phần đã gọi (từ `donhangct`, bỏ đơn hủy), số lần xem (`luotxem_nguoidung`, hoặc cookie với khách chưa đăng nhập). Lịch sử xem trong cookie được nhập vào hồ sơ sau khi khách đăng nhập/đăng ký.
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
| 12 | Khách đánh giá món không nằm trong đơn hoàn thành của mình | Bị từ chối |
| 13 | Gửi hai đánh giá cho cùng món trong một đơn | Chỉ lưu một đánh giá |
| 14 | Tắt sự kiện hoặc xem ngoài thời hạn/chi nhánh áp dụng | Không hiển thị ở trang sự kiện khách hàng |
| 15 | Xác nhận hai yêu cầu đặt bàn chồng lấn vượt sức chứa | Yêu cầu vượt ngưỡng bị từ chối; yêu cầu rời giờ không bị tính chồng |
| 16 | Hủy đơn và kiểm tra nhật ký | Tồn kho/voucher được hoàn đúng một lần, lịch sử ghi tác nhân |
| 17 | Đơn chuyển khoản còn hạn | Hiện QR Vietcombank đúng số tiền và nội dung mã đơn |
| 18 | Nhân viên xác nhận thanh toán từ chi nhánh khác | Bị từ chối |
| 19 | Nhân viên xác nhận đơn chuyển khoản quá hạn/đã hủy | Bị từ chối, đơn không được đưa vào bếp |
| 20 | Tác vụ xử lý đơn chuyển khoản hết hạn | Hủy đơn và hoàn tồn kho/voucher đúng một lần |
| 21 | Nhân viên phản hồi đánh giá | Khách thấy phản hồi trên chi tiết món; đánh giá ẩn không hiển thị |

## Giai đoạn 6 - Triển khai (Tuần 8)
Hosting PHP 7.4+/MySQL, khởi tạo database mới bằng `database/goldengate.sql`, kiểm tra tài khoản Vietcombank trong cấu hình, tắt debug, bật HTTPS, đổi mật khẩu mẫu và thiết lập sao lưu. Cấu hình Task Scheduler/cron chạy `php bin/expire_bank_transfers.php` mỗi phút.

## Giai đoạn 7 - Bảo trì và mở rộng
Hoàn thiện quy trình hoàn tiền trên cổng thanh toán, thông báo email/Zalo, màn hình bếp (KDS), thống kê chuyên sâu và chỉnh sửa đánh giá sau khi gửi.

## Rủi ro chính
| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Bán vượt tồn kho | Cao | Khóa dòng trong giao dịch (đã làm) |
| Đặt bàn vượt sức chứa khi nhiều nhân viên xác nhận | Cao | Khóa chi nhánh và kiểm tra mức khách chồng lấn trong giao dịch |
| Nhân viên xác nhận nhầm giao dịch chuyển khoản | Cao | Đối chiếu sao kê, số tiền và nội dung chuyển khoản trước khi xác nhận |
| Lộ dữ liệu giữa chi nhánh | Cao | Kiểm tra chi nhánh ở tầng model + controller (đã làm) |
| Trễ tiến độ do mở rộng phạm vi | TB | Chốt phạm vi bản 1, đưa phần còn lại vào bản 2 |
| Dữ liệu cá nhân hóa ít khi mới chạy | TB | Fallback món bán chạy (đã làm) |
