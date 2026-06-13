<?php
/**
 * MVP - View: vAdminTypes
 * Nhận $types[] từ Presenter, render bảng thương hiệu.
 */
?>
<?php if (empty($types)): ?>
    <p>Không có dữ liệu thương hiệu</p>
<?php else: ?>
    <table class="admin-table">
        <tr><th>ID</th><th>Tên thương hiệu</th><th>Thao Tác</th></tr>
        <?php foreach ($types as $r): ?>
        <tr>
            <td><?= (int)$r['idType'] ?></td>
            <td><?= htmlspecialchars($r['typeName']) ?></td>
            <td class="action-cell">
                <a href="?suath&id=<?= (int)$r['idType'] ?>">Sửa</a> |
                <a href="?xoath&id=<?= (int)$r['idType'] ?>"
                   onclick="return confirm('Bạn có chắc chắn muốn xóa thương hiệu này không?')">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
