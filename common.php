<?php
session_name('qlnhansu_session');
session_start();
header('Cache-Control: no-store');
require __DIR__ . '/connect.php';
$_SESSION['token'] = $_SESSION['token'] ?? bin2hex(random_bytes(32));
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function is_admin(): bool {
    global $conn;

    if (!$conn || empty($_SESSION['admin_id'])) {
        return false;
    }

    $stmt = $conn->prepare('SELECT admin_id FROM Users WHERE admin_id = ? AND admin_email = ?');
    $stmt->execute([$_SESSION['admin_id'], 'admin']);

    return (bool) $stmt->fetch();
}

function token_field(): void {
    ?>
    <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">
    <?php
}
function valid_token(): bool {
    return hash_equals($_SESSION['token'], (string)($_POST['token'] ?? ''));
}
function photo_url(string $path): string {
    // Chỉ hiển thị ảnh nội bộ; không chấp nhận URL hay đường dẫn tùy ý.
    return preg_match('~^(?:assets|uploads)/[a-zA-Z0-9_.-]+\.(?:svg|png|jpe?g|webp)$~iD', $path) ? $path : 'assets/avatar.svg';
}
function page_header(string $title): void {
    global $connectionError;
    $notice = $_SESSION['notice'] ?? '';
    unset($_SESSION['notice']);
    $loggedIn = is_admin();
    ?>
<!doctype html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · QLNhânSự</title>
        <link rel="stylesheet" href="style.css?v=6">
    </head>
    <body>
        <header class="site-header">
            <a class="site-brand" href="index.php">Quản lý nhân sự</a>
            <nav class="account-nav" aria-label="Tài khoản">
                <?php if ($loggedIn): ?>
                    <span class="admin-status">Quản trị viên</span>
                    <form method="post" action="logout.php">
                        <?php token_field(); ?>
                        <button class="account-button" type="submit">Đăng xuất</button>
                    </form>
                <?php elseif ($title !== 'Đăng nhập quản trị'): ?>
                    <a class="button account-button" href="login.php">Đăng nhập quản trị</a>
                <?php else: ?>
                    <a class="back-list" href="index.php">← Xem danh sách</a>
                <?php endif; ?>
            </nav>
        </header>
        <main>
            <?php if ($connectionError): ?>
                <p class="alert error" role="alert"><?= e($connectionError) ?></p>
            <?php endif; ?>
            <?php if ($notice): ?>
                <p class="alert success" role="status"><?= e($notice) ?></p>
            <?php endif; ?>
<?php
}

function page_end(): void {
    ?>
        </main>
        <script src="app.js?v=2"></script>
    </body>
</html>
<?php
}
