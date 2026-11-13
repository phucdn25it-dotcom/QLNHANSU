<?php
// Chỉ kết nối: không tự tạo database, bảng hay dữ liệu.
$config = require __DIR__ . '/config.php';
$conn = null;
$connectionError = '';
try {
    $conn = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $conn->query('SELECT admin_id FROM Users LIMIT 1');
    $idColumn = $conn->query("SHOW COLUMNS FROM Users LIKE 'admin_id'")->fetch();

    if (strtolower($idColumn['Type'] ?? '') !== 'varchar(20)') {
        $conn = null;
        $connectionError = 'Cột Users.admin_id vẫn là kiểu số nên ND001 bị lưu thành 0. Vào phpMyAdmin → QLNhanSu → SQL, chạy ALTER TABLE Users MODIFY admin_id VARCHAR(20) NOT NULL; rồi sửa mã 0 thành ND001 trong bảng Users.';
    }
} catch (PDOException $error) {
    $conn = null;
    $connectionError = 'Chưa kết nối được bảng Users. Bật MySQL trong XAMPP, tạo database và bảng trong phpMyAdmin theo README.md; kiểm tra config.php.';
}
