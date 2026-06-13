<?php
/**
 * MVP - View: vAdminUser
 * Nhận $users[] từ Presenter, render bảng người dùng.
 */
?>
<?php if (empty($users)): ?>
    <p>Không có dữ liệu người dùng</p>
<?php else: ?>
    <table class="admin-table">
        <tr><th>Mã người dùng</th><th>Tên Người dùng</th><th>Vai trò</th></tr>
        <?php foreach ($users as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td><?= htmlspecialchars($r['nameRole']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
