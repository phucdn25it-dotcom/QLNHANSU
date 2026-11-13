<?php
require __DIR__ . '/common.php';
require_admin();
if (!isset($mode) || !in_array($mode, ['add', 'edit', 'delete'], true)) {
    http_response_code(404);
    exit;
}
$id = trim((string)($_GET['id'] ?? ''));
$row = ['admin_email' => '', 'images' => 'assets/avatar.svg'];
$error = '';
$available = $conn !== null;
if ($conn && $mode !== 'add') {
    $stmt = $conn->prepare('SELECT * FROM Users WHERE admin_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        $error = 'Không tìm thấy người dùng.';
        $available = false;
    }
}
$email = trim((string)($_POST['admin_email'] ?? ($row['admin_email'] ?? '')));
$userCode = strtoupper(trim((string)($_POST['admin_id'] ?? ($mode === 'add' ? '' : $id))));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $available) {
    $uploaded = '';
    try {
        if (!valid_token()) throw new RuntimeException('Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.');
        if ($mode === 'delete') {
            $stmt = $conn->prepare('DELETE FROM Users WHERE admin_id = ?');
            $stmt->execute([$id]);
            $_SESSION['notice'] = 'Đã xóa người dùng.';
        }
        else {
            if (!preg_match('/^ND[0-9]{1,18}$/D', $userCode)) {
                throw new RuntimeException('Mã người dùng phải bắt đầu bằng ND và theo sau là số, ví dụ ND123 (tối đa 20 ký tự).');
            }

            $stmt = $conn->prepare('SELECT admin_id FROM Users WHERE admin_id = ? AND admin_id <> ?');
            $stmt->execute([$userCode, $mode === 'edit' ? $id : '']);
            if ($stmt->fetch()) {
                throw new RuntimeException('Mã người dùng đã tồn tại. Vui lòng nhập mã khác.');
            }

            if (($email !== 'admin' && !filter_var($email, FILTER_VALIDATE_EMAIL)) || strlen($email) > 255) {
                throw new RuntimeException('Nhập email hợp lệ hoặc tài khoản admin (tối đa 255 ký tự).');
            }

            $stmt = $conn->prepare('SELECT admin_id FROM Users WHERE admin_email = ? AND admin_id <> ? LIMIT 1');
            $stmt->execute([$email, $mode === 'edit' ? $id : '']);
            if ($stmt->fetch()) {
                throw new RuntimeException('Email hoặc tài khoản này đã tồn tại.');
            }
            $password = (string)($_POST['admin_password'] ?? '');
            if (($mode === 'add' || $password !== '') && (strlen($password) < 6 || strlen($password) > 72)) throw new RuntimeException('Mật khẩu cần từ 6 đến 72 byte.');
            $image = $row['images'];
            $file = $_FILES['images'] ?? ['error' => UPLOAD_ERR_NO_FILE];
            if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) throw new RuntimeException('Tải ảnh thất bại. Chọn ảnh tối đa 2 MB.');
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) throw new RuntimeException('Chỉ nhận ảnh JPG, PNG hoặc WebP hợp lệ.');
                $uploaded = 'uploads/' . bin2hex(random_bytes(16)) . '.' . $types[$mime];
                if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $uploaded)) throw new RuntimeException('Không lưu được ảnh. Kiểm tra quyền ghi thư mục uploads.');
                $image = $uploaded;
            }
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : $row['admin_password'];
            if ($mode === 'add') {
                $stmt = $conn->prepare('INSERT INTO Users (admin_id, admin_email, admin_password, images) VALUES (?, ?, ?, ?)');
                $stmt->execute([$userCode, $email, $hash, $image]);
            }
            else {
                $stmt = $conn->prepare('UPDATE Users SET admin_id = ?, admin_email = ?, admin_password = ?, images = ? WHERE admin_id = ?');
                $stmt->execute([$userCode, $email, $hash, $image, $id]);

                if ((string)($_SESSION['admin_id'] ?? '') === $id) {
                    $_SESSION['admin_id'] = $userCode;
                }
            }
            $_SESSION['notice'] = $mode === 'add' ? 'Đã thêm người dùng vào MySQL.' : 'Đã cập nhật người dùng trong MySQL.';
        }
        header('Location: admin.php');
        exit;
    }
    catch (Throwable $ex) {
        if ($uploaded && is_file(__DIR__ . '/' . $uploaded)) unlink(__DIR__ . '/' . $uploaded);
        $error = $ex instanceof PDOException
            ? ($ex->getCode() === '23000' ? 'Mã người dùng đã tồn tại.' : 'Không lưu được dữ liệu. Hãy nhập lại database.sql trong phpMyAdmin để cập nhật cột mã.')
            : $ex->getMessage();
    }
}
$title = ['add' => 'Thêm người dùng', 'edit' => 'Sửa người dùng', 'delete' => 'Xóa người dùng'][$mode];
page_header($title);
?>
<section class="panel form-panel">
    <h1><?= e($title) ?></h1>
    <?php if ($error): ?>
        <p class="alert error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <?php if ($available): ?>
        <form method="post" enctype="multipart/form-data" class="stack">
            <?php token_field(); ?>
            <?php if ($mode === 'delete'): ?>
                <p>
                    Bạn có chắc muốn xóa người dùng
                    <strong>
                        <?= e($row['admin_email']) ?>
                    </strong>
                    (Mã
                    <?= e($id) ?>
                    )?
                </p>
                <p>Thao tác này xóa bản ghi khỏi bảng Users trong MySQL.</p>
            <?php else: ?>
                <label for="admin_id">Mã người dùng *</label>
                <input
                    id="admin_id"
                    type="text"
                    name="admin_id"
                    required
                    maxlength="20"
                    pattern="[Nn][Dd][0-9]{1,18}"
                    placeholder="Ví dụ: ND123"
                    value="<?= e($userCode) ?>"
                >
                <small>Tự nhập mã bắt đầu bằng ND và theo sau là số. Mã không được trùng.</small>
                <label for="email">Email / Tài khoản *</label>
                <input id="email" type="text" name="admin_email" required maxlength="255" value="<?= e($email) ?>" placeholder="nguoidung@example.com hoặc admin">
                <label for="password">Mật khẩu <?= $mode === 'add' ? '*' : '(để trống để giữ nguyên)' ?></label>
                <div class="password-field">
                    <input id="password" type="password" name="admin_password" minlength="6" maxlength="72" autocomplete="new-password" <?= $mode === 'add' ? 'required' : '' ?>>
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-controls="password" aria-pressed="false">Hiện</button>
                </div>
                <small>
                    Mật khẩu được lưu dưới dạng mã băm, không hiển thị công khai.
                </small>
                <label for="images">Ảnh đại diện</label>
                <div class="upload">
                    <img class="avatar preview" id="preview" src="<?= e(photo_url($row['images'])) ?>" alt="Xem trước ảnh" data-avatar>
                    <input id="images" type="file" name="images" accept="image/jpeg,image/png,image/webp">
                    <small>
                        JPG, PNG, WebP · tối đa 2 MB. Để trống để giữ ảnh hiện tại.
                    </small>
                </div>
            <?php endif; ?>
            <div class="form-actions">
                <a href="admin.php">← Quay lại</a>
                <button>
                    <?= $mode === 'delete' ? 'Xác nhận xóa' : 'Lưu dữ liệu' ?>
                </button>
            </div>
        </form>
    <?php else: ?>
        <a href="admin.php">← Quay lại danh sách</a>
    <?php endif; ?>
</section>
<?php
page_end();
?>
