-- CƠ SỞ DỮ LIỆU QUẢN LÝ NHÂN SỰ
-- Nhập file này bằng tab Import hoặc SQL trong phpMyAdmin.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS QLNhanSu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE QLNhanSu;

-- Mã người dùng tự nhập: ND001, ND002, ND123...
CREATE TABLE IF NOT EXISTS Users (
    admin_id       VARCHAR(20)  NOT NULL PRIMARY KEY,
    admin_email    VARCHAR(255) NOT NULL,
    admin_password VARCHAR(255) NOT NULL,
    images         VARCHAR(255) NOT NULL DEFAULT 'assets/avatar.svg'
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

-- Tài khoản quản trị mẫu: admin / admin123.
-- Mật khẩu lưu dạng bcrypt, không lưu mật khẩu thuần.
-- Chỉ thêm khi chưa có admin và mã ND001 chưa được sử dụng.
-- Chạy lại không xóa hoặc ghi đè dữ liệu hiện có.
INSERT INTO Users (admin_id, admin_email, admin_password, images)
SELECT
    'ND001',
    'admin',
    '$2y$10$1/X4C07UenCBmby01S9CtudXOB/8aL90.dy3LhenatDymgLm7D5Z.',
    'assets/avatar.svg'
WHERE NOT EXISTS (
    SELECT 1
    FROM Users
    WHERE admin_email = 'admin' OR admin_id = 'ND001'
);
