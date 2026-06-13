<?php
/**
 * MVP - View: vAdminProducts
 * Nhận $products[] từ Presenter, render bảng quản trị.
 */
?>
<?php if (empty($products)): ?>
    <p>Không có sản phẩm</p>
<?php else: ?>
    <table class="admin-table">
        <tr>
            <th>ID</th><th>Tên sản phẩm</th><th>Giá</th>
            <th>Giá sale</th><th>Hình</th><th>Loại</th>
            <th class="action-cell">Thao tác</th>
        </tr>
        <?php foreach ($products as $r): ?>
        <tr>
            <td><?= (int)$r['idProduct'] ?></td>
            <td><?= htmlspecialchars($r['productName']) ?></td>
            <td><?= htmlspecialchars($r['productPrice']) ?></td>
            <td><?= htmlspecialchars($r['salePrice']) ?></td>
            <td><?= $r['image'] ? "<img src='image/{$r['image']}?v=".time()."' width='60'/>" : '' ?></td>
            <td><?= htmlspecialchars($r['typeName']) ?></td>
            <td class="action-cell">
                <a href="?suasp&id=<?= (int)$r['idProduct'] ?>">Sửa</a> |
                <a href="?xoasp&id=<?= (int)$r['idProduct'] ?>"
                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
