# GAME Admin

## Cài MySQL/MariaDB bằng phpMyAdmin

1. Trong cPanel, tạo database MySQL và user, sau đó cấp **ALL PRIVILEGES** cho user đó.
2. Mở phpMyAdmin, chọn database vừa tạo rồi import file `install.sql`.
3. Sao chép `database.config.example.php` thành `database.config.php`.
4. Điền `host`, `database`, `username`, `password` trong `database.config.php` theo thông tin hosting.
5. Upload toàn bộ thư mục `WEBSITE` lên hosting. Không đưa `database.config.php` lên GitHub.

Khi kết nối thành công, web admin lưu cấu hình trong bảng `app_config`. Lần đầu chạy, dữ liệu hiện có từ `config.json` được tự động nhập vào MySQL. Mỗi lần lưu, hệ thống vẫn cập nhật `config.json` làm bản sao dự phòng. Hosting cần PHP 8+, extension `pdo_mysql`, MySQL hoặc MariaDB.

Nếu chưa tạo `database.config.php`, web dùng `config.json` để cài thử. Sau khi đã cấu hình MySQL, lỗi kết nối sẽ được báo rõ và hệ thống không tự chuyển về JSON.

> Sau khi đã tạo `database.config.php`, hệ thống chỉ dùng MySQL. Nếu database mất kết nối, admin sẽ báo lỗi thay vì âm thầm mở `config.json` mặc định và làm dữ liệu trông như bị reset. Khi cập nhật bản ZIP, không xóa file `database.config.php` đang nằm trên hosting và không import lại database mới.

Trong trang **Giao diện**, có thể sửa tên, mô tả và link avatar của khối đầu trang chủ. Trong trang **Thanh tab**, mỗi ứng dụng quản lý một danh sách tab riêng; thêm, sửa hoặc xóa tab của ứng dụng này không làm thay đổi ứng dụng khác.

1. Upload toàn bộ nội dung thư mục này lên hosting có PHP 8+.
2. Cấu hình biến môi trường `APEX_ADMIN_PASSWORD` trên hosting trước khi sử dụng; không ghi mật khẩu thật vào source hoặc GitHub.
3. Cấp quyền ghi cho `config.json` và thư mục `uploads/` nếu hosting yêu cầu.
4. Mở **`index.php`** để xem landing page; trang quản trị nằm tại **`/admin/`**.
5. Mở **`/admin/`**, đăng nhập mật khẩu → sidebar:
   - **Thông báo** — popup khi vào app
   - **Giao diện** — nhạc nền (link/upload hoặc lấy tiếng từ video), ảnh/video nền
   - **GAME** — danh sách game (không còn hiện notice trong app tab Game)
   - **Danh mục** — AIM / Định vị / Mod + file `.3105` + mật khẩu gói

### Giao diện app (config.json)
```json
"musicEnabled": true,
"musicSource": "audio",
"musicURL": "https://.../song.mp3",
"musicFromVideoURL": "",
"backgroundType": "image",
"backgroundImageURL": "https://.../bg.jpg",
"backgroundVideoURL": "https://.../bg.mp4"
```
- App tự phát nhạc khi mở nếu `musicEnabled=true` (user vẫn tắt được trong Cài đặt).
- `musicSource=audio` phát `musicURL`; `musicSource=video` phát audio track từ `musicFromVideoURL`.
- Video nền lặp im lặng; ảnh/video phủ toàn app.
5. App gọi **`config.php`** qua HTTPS. URL endpoint đã được tách khỏi `Info.plist` và obfuscate trong binary để tránh lộ dưới dạng chuỗi thuần.
6. Lưu trên web → mở lại app / kéo xuống refresh là thấy ngay, **không** cần build IPA lại.

`config.json` được admin ghi; app đọc qua `config.php` (header no-cache). Giới hạn upload: **80 MB** (nhạc/ảnh/video/.3105).

## Cập nhật live (không rebuild)

| Việc | Cách |
|------|------|
| Sửa thông báo / game / aim | Lưu trên web admin |
| App nhận | Mỗi lần mở app / về foreground / pull-to-refresh tab Game |
| IPA cần build lại chỉ khi | Đổi **code Swift** (logic app), không phải nội dung web |

## Onboarding & thông báo (app)

- **Cài mới**: chỉ màn **chọn ngôn ngữ** (1 lần).
- **Thông báo** (`noticeTitle` / `noticeMessage` trong config): hiện mỗi lần vào app.
  - **OK**: đóng lần này, hiện lại lần vào sau.
  - **Ẩn 1 giờ**: không hiện trong 60 phút.

## Nút AIM / Định vị / Mod trong app

Mỗi item cần `fileURL` trỏ thẳng tới file **`.3105`** (cùng format tab Patches).

Luồng app:
1. Vào app / pull-to-refresh → tải config + **prefetch** `.3105`.
2. **Bật toggle** → `DevicePatchService.apply`.
3. **Tắt toggle** → `DevicePatchService.restore`.

Ví dụ upload:
```
/APEX_IPA/config.php
/APEX_IPA/config.json
/APEX_IPA/packages/aim-drag.3105
/APEX_IPA/packages/location-magic.3105
/APEX_IPA/packages/modskin.3105
/APEX_IPA/packages/Aim-Body-FFMax.3105
```

Trong `config.json`:
```json
{
  "id": "aim-body-ffmax",
  "name": "Aim Body FF Max",
  "category": "aim",
  "fileURL": "https://huutien.store/APEX_IPA/packages/Aim-Body-FFMax.3105"
}
```

## Mật khẩu file `.3105`

Trên admin web, mỗi mục AIM/Định vị/Mod có ô **Mật khẩu file .3105**:
- Gói **không khóa** → để trống.
- Gói **có khóa** → nhập đúng mật khẩu lúc export `.3105`.
- App đọc `packagePassword` từ config và tự mở gói khi bật toggle (user không cần gõ lại).

Lưu ý: không truy cập trực tiếp `config.json` trên hosting. Nên chặn file này bằng cấu hình web server và chỉ cho app đọc dữ liệu qua `config.php`.
