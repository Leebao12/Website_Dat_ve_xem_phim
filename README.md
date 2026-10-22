# CINEMAX — Website Đặt vé xem phim

Đồ án môn **Lập trình mã nguồn mở**, dựa trên báo cáo `BaoCao_LTMNM(1).docx` đến hết Chương 2.

**Công nghệ:** PHP >= 8.3, Laravel 13, MySQL 8 / MariaDB tương thích, Eloquent ORM, Migration/Seeder, Blade, Bootstrap 5 và CSS/JavaScript. **Không dùng Vite/npm** để giảm bước cài. Bootstrap & font được tải từ CDN; cần Internet để hiển thị những phần đó (phần CSS CINEMAX cốt lõi lưu tại `public/css/cinemax.css`).

> Dự án gồm source Laravel và hình ảnh; **không kèm thư mục `vendor`**. Máy đóng gói không có Composer và không kết nối được Packagist, vì vậy chưa thể chạy end-to-end ở đây. Anh cần chạy `composer install` trên máy Laragon có Internet. Đã kiểm tra cú pháp toàn bộ file PHP bằng `php -l`.

## 1. Cài đặt trên Laragon (Windows)

1. Cài **Laragon + PHP 8.3 trở lên + Composer**. Mở Laragon -> Start All.
2. Giải nén thư mục `cinemax` vào `C:\laragon\www\cinemax`.
3. Mở Laragon Terminal trong thư mục dự án, chạy:

```powershell
cd C:\laragon\www\cinemax
composer install
copy .env.example .env
php artisan key:generate
```

4. Mở phpMyAdmin/HeidiSQL trong Laragon tạo database MySQL tên **cinemax** với charset `utf8mb4`, hoặc chạy:

```sql
CREATE DATABASE cinemax CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. Mở file `.env` ở **thư mục gốc `cinemax`**, chỉnh `DB_HOST`, `DB_DATABASE=cinemax`, `DB_USERNAME`, `DB_PASSWORD` theo MySQL của anh. Mặc định Laragon có thể là `root` / mật khẩu trống nhưng phải đối chiếu trên máy anh.
6. Chạy:

```powershell
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

7. Mở **http://127.0.0.1:8000**. Laragon có thể bật virtual host `cinemax.test` nếu document root trỏ đúng vào `public`.

**Nếu báo thiếu `ext-mbstring`, `ext-pdo_mysql`, `openssl`, `fileinfo`, `zip`:** bật extension phù hợp trong Laragon -> PHP -> Extensions và restart.

## 2. Tài khoản demo (CHỈ DÙNG LOCAL)

| Vai trò | Email | Mật khẩu |
| --- | --- | --- |
| Quản trị viên | `admin@cinemax.test` | `Demo@12345` |
| Quản lý | `manager@cinemax.test` | `Demo@12345` |
| Khách hàng | `customer@cinemax.test` | `Demo@12345` |

Seeder tạo 4 phim từ poster anh cung cấp, 3 phòng, 40 ghế/phòng, các suất 7 ngày và 5 sản phẩm bắp nước. **Đổi/xóa tài khoản demo trước khi triển khai thật.**

## 3. Chức năng đã viết

### Khách hàng
- Trang chủ, phim đang/sắp chiếu; tìm kiếm, lọc thể loại/trạng thái; chi tiết phim, lịch chiếu 7 ngày.
- Xem phòng chiếu và danh sách suất tại từng phòng (do CSDL hiện có `rooms` nhưng **chưa có bảng chi nhánh/rạp**).
- Đăng ký, đăng nhập, đăng xuất, hồ sơ, đổi mật khẩu.
- Sơ đồ ghế thường/VIP, chọn 1–8 ghế, kiểm tra ghế bận; đặt thêm bắp nước/combo; tính lại tổng tiền ở máy chủ.
- Giữ ghế **15 phút** khi chờ thanh toán; chống đặt trùng đồng thời bằng transaction + `lockForUpdate()` trên `showtimes` (MySQL/InnoDB).
- Tạo đơn và giao dịch, thanh toán **sandbox (mô phỏng, không chuyển tiền)**, xem lịch sử và chi tiết mã đặt vé. Không hỗ trợ khách hủy vé đã thanh toán.

### Quản lý (`quan_ly`)
- Dashboard tổng quan, thống kê doanh thu theo ngày/khoảng ngày.
- CRUD phim + poster tải lên + thể loại; CRUD thể loại.
- CRUD phòng/ghế, loại ghế, giá ghế, unique `(room_id, seat_code)`, đồng bộ `total_seats`.
- CRUD suất chiếu; kiểm tra trùng lịch theo thời lượng phim và 15 phút vệ sinh phòng; không xóa lịch có đơn liên kết.
- CRUD sản phẩm bắp nước; danh sách đơn/tìm kiếm/lọc/trạng thái, chi tiết/hủy đơn chờ thanh toán.

### Admin (`admin`)
- Danh sách người dùng, tìm kiếm, phân vai trò, khóa/mở tài khoản; cấm tự khóa/hạ quyền admin đang đăng nhập.

## 4. Thanh toán API & giới hạn quan trọng

- **Có chế độ demo dùng thật trong ứng dụng nhưng KHÔNG kết nối ngân hàng thật.** Nút `Thanh toán sandbox` tạo giao dịch `SANDBOX-...` và cập nhật trạng thái.
- Có endpoint webhook giáo dục `POST /api/payments/webhook` sử dụng HMAC-SHA256 ký toàn bộ raw JSON qua header `X-CINEMAX-SIGNATURE`. Đặt `PAYMENT_WEBHOOK_SECRET` bí mật, không dùng giá trị mặc định. Body:

```json
{"booking_code":"CMX-...","transaction_code":"TEST-GW-123","amount":85000,"status":"thanh_cong"}
```

- **Webhook này là giao thức demo do dự án định nghĩa**, chưa tương thích sẵn với VNPay/MoMo/SePay vì mỗi bên có cơ chế API, chữ ký, field, kiểm chứng, callback riêng. Muốn dùng cổng thật phải bổ sung adapter riêng và nhận credentials sandbox.
- Chưa triển khai hoàn tiền tự động, quét vé tại cổng, quản lý thiết bị rạp hay đa chi nhánh, đúng giới hạn báo cáo.
- Đây là mã nguồn đồ án, **chưa kiểm thử end-to-end trong máy đóng gói**. Trước khi công bố cần chạy kiểm thử trên môi trường Laragon thực tế.

## 5. Cấu trúc

```
app/Models/                      12 Eloquent models (bao gồm User)
app/Http/Controllers/            Website, đặt vé, tài khoản, webhook
app/Http/Controllers/Manager/    CRUD, dashboard, doanh thu
app/Http/Controllers/Admin/      Quản lý người dùng
bootstrap/app.php                Route + middleware vai trò
config/                          Cấu hình Laravel + cinemax.php
database/migrations/             Migration cho 12 bảng
database/seeders/                Phim, phòng, ghế, demo users
resources/views/                 Giao diện Blade nhiều trang
public/assets/                  Logo, banner, poster anh cung cấp
public/css/                     CINEMAX dark cinema UI
public/js/seats.js               Tương tác chọn ghế
routes/web.php                   Routes
```

## 6. Các lệnh kiểm tra / lỗi hay gặp

```powershell
php -v
composer --version
php artisan migrate:status
php artisan route:list
php artisan view:cache
php artisan view:clear
php artisan optimize:clear
```

- `could not find driver`: bật `pdo_mysql`.
- `Access denied for user`: sửa `.env` DB_USERNAME/DB_PASSWORD, sau đó `php artisan config:clear`.
- `No application encryption key`: `php artisan key:generate`.
- `Class not found`: `composer dump-autoload`.
- Ảnh do admin tải lên không hiện: `php artisan storage:link`.
- Trang trống / 500: xem `storage/logs/laravel.log`, gửi log để chẩn đoán chính xác.
- Không chạy `migrate:fresh --seed` trên database chứa dữ liệu cần giữ: nó sẽ **xóa toàn bộ bảng/dữ liệu**.

## 7. Các trường hợp thử nghiệm được khuyến nghị

1. Đăng ký tài khoản; đăng nhập và cập nhật hồ sơ; đổi mật khẩu.
2. Lọc phim; mở chi tiết; nhấn ngày và suất; chọn ghế A01; chọn combo; tạo đơn; thanh toán sandbox; xem Vé của tôi.
3. Mở hai trình duyệt, hai tài khoản cùng đặt một ghế trong cùng suất; tài khoản đến sau nhận thông báo ghế bận.
4. Thử đặt lại ghế A01 cho suất khác; vẫn được.
5. Quản lý tạo phòng và ghế trùng mã -> validation. Thử tạo suất bị trùng thời gian -> validation.
6. Quản lý xóa phòng có suất chiếu và phim có suất chiếu -> bị chặn.
7. Admin khóa tài khoản; kiểm tra user không đăng nhập mới được.

**Lưu ý:** Nếu anh đang có DB MySQL theo báo cáo, hãy sao lưu trước. Migration này tự tạo 12 bảng; đừng chạy vào database cũ khác cấu trúc nếu chưa so sánh migration.
