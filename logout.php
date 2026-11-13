<?php
require __DIR__ . '/common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !valid_token()) {
    http_response_code(403);
    exit('Yêu cầu không hợp lệ.');
}
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
