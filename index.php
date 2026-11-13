<?php
require __DIR__ . '/common.php';

$isAdminPage = $isAdminPage ?? false;
if ($isAdminPage) {
    require_admin();
}
$canManage = is_admin();

$q = trim((string)($_GET['q'] ?? ''));
$rows = [];

if ($conn) {
    $stmt = $conn->prepare(
        'SELECT admin_id, admin_email, images FROM Users
         WHERE admin_id LIKE ? OR admin_email LIKE ?
         ORDER BY admin_id ASC'
    );
    $stmt->execute(['%' . $q . '%', '%' . $q . '%']);
    $rows = $stmt->fetchAll();
}

$listUrl = $isAdminPage ? 'admin.php' : 'index.php';
page_header('Danh sách người dùng');
?>

<section class="panel reference-list">
    <div class="list-heading">
        <h1>Danh sách người dùng</h1>
    </div>

    <div class="list-toolbar">
        <form method="get" action="<?= e($listUrl) ?>" class="search">
            <label class="sr-only" for="q">Tìm kiếm theo mã hoặc email người dùng</label>
            <input
                id="q"
                name="q"
                type="search"
                placeholder="Tìm kiếm theo mã hoặc email người dùng..."
                value="<?= e($q) ?>"
            >
            <button class="sr-only" type="submit">Tìm kiếm</button>

            <?php if ($q !== ''): ?>
                <a href="<?= e($listUrl) ?>">Xóa lọc</a>
            <?php endif; ?>
        </form>

        <?php if ($canManage): ?>
            <a class="button add-user" href="them.php">Thêm Người Dùng</a>
        <?php endif; ?>
    </div>

    <div class="table-wrap">
        <table class="<?= $canManage ? 'admin-table' : 'public-table' ?>">
            <thead>
                <tr>
                    <th>Mã người dùng</th>
                    <th>Hình ảnh</th>
                    <th class="user-email">Email / Tài khoản</th>
                    <?php if ($canManage): ?>
                        <th>Mật khẩu</th>
                        <th>Thao tác</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <span class="id"><?= e($row['admin_id']) ?></span>
                        </td>
                        <td>
                            <img
                                class="avatar"
                                src="<?= e(photo_url($row['images'])) ?>"
                                alt="Ảnh đại diện người dùng <?= e($row['admin_id']) ?>"
                                data-avatar
                            >
                        </td>
                        <td class="user-email"><?= e($row['admin_email']) ?></td>
                        <?php if ($canManage): ?>
                            <td>
                                <span class="password-mask" aria-label="Mật khẩu được ẩn">••••••••</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="sua.php?id=<?= e($row['admin_id']) ?>">Sửa</a>
                                    <a class="danger" href="xoa.php?id=<?= e($row['admin_id']) ?>">Xóa</a>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="<?= $canManage ? 5 : 3 ?>" class="empty">
                            <?= $conn ? 'Chưa có người dùng phù hợp.' : 'Chưa tải được dữ liệu từ MySQL.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php page_end(); ?>
