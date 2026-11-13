<?php
require __DIR__ . '/common.php';
if (is_admin()) {
    header('Location: admin.php');
    exit;
}
$error = '';
$email = trim((string)($_POST['email'] ?? ''));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_token()) {
        $error = 'Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
    } elseif (!$conn) {
        $error = 'Chưa kết nối được cơ sở dữ liệu.';
    } else {
        $stmt = $conn->prepare('SELECT admin_id, admin_password FROM Users WHERE admin_email = ? ORDER BY admin_id');
        $stmt->execute([$email]);
        $account = $stmt->fetch();

        if ($email === 'admin' && $account && password_verify((string)($_POST['password'] ?? ''), $account['admin_password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $account['admin_id'];
            $_SESSION['token'] = bin2hex(random_bytes(32));
            header('Location: admin.php');
            exit;
        }

        $error = 'Tài khoản hoặc mật khẩu quản trị không đúng.';
    }
}
page_header('Đăng nhập quản trị');
?>
<section class="panel form-panel">
    <h1>Đăng nhập</h1>
    <p>Đăng nhập bằng tài khoản admin trong bảng Users.</p>
    <?php if ($error): ?>
        <p class="alert error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" class="stack">
        <?php token_field(); ?>
        <label for="email">Tài khoản quản trị</label>
        <input id="email" name="email" type="text" required maxlength="255" autocomplete="username" placeholder="admin" value="<?= e($email) ?>">
        <label for="password">Mật khẩu</label>
        <div class="password-field">
            <input id="password" name="password" type="password" required autocomplete="current-password">
            <button type="button" class="password-toggle" data-password-toggle="password" aria-controls="password" aria-pressed="false">Hiện</button>
        </div>
        <button>Đăng nhập</button>
        <a href="index.php">← Xem danh sách người dùng</a>
    </form>
</section>
<?php
page_end();
?>
