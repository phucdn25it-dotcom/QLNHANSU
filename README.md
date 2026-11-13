# Quản lý nhân sự

Bài thực hành PHP và MySQL, quản lý bảng Users trong database QLNhanSu.

## Chạy bằng XAMPP

1. Clone repository vào thư mục htdocs, ví dụ `C:/xampp/htdocs/QLNHANSU`.
2. Bật Apache và MySQL trong XAMPP.
3. Mở `http://localhost/phpmyadmin/`, chọn Import và nhập `database.sql`.
4. Kiểm tra thông số MySQL trong `config.php`. Mặc định localhost, root, mật khẩu trống.
5. Truy cập `http://localhost/QLNHANSU/`.

Tài khoản mẫu: **admin / admin123**. File SQL không ghi đè tài khoản hoặc dữ liệu hiện có. Đây là cấu hình cho bài thực hành trên localhost.

## Chức năng

- Khách xem danh sách, ảnh đại diện và tìm kiếm theo mã hoặc email.
- Quản trị đăng nhập để thêm, sửa, xóa người dùng.
- Mã người dùng tự nhập dạng ND001, ND123; không được trùng.
- Mật khẩu lưu bằng password_hash, đăng nhập kiểm tra bằng password_verify.
- Tải ảnh JPG, PNG, WebP tối đa 2 MB.
- Có thể thao tác dữ liệu trực tiếp bằng phpMyAdmin rồi tải lại trang web.

## Các file chính

- `config.php`, `connect.php`: cấu hình và kết nối MySQL.
- `common.php`: phiên đăng nhập, phân quyền, CSRF và giao diện dùng chung.
- `index.php`, `admin.php`: danh sách công khai và quản trị.
- `user.php`: form và xử lý thêm/sửa/xóa dùng chung, được gọi từ `them.php`, `sua.php`, `xoa.php`.
- `login.php`, `logout.php`: đăng nhập, đăng xuất.
- `database.sql`: cấu trúc bảng và tài khoản mẫu, nhập qua phpMyAdmin.

Ảnh tải lên trong uploads không đưa vào Git. Bản clone mới dùng ảnh mặc định trong assets.
