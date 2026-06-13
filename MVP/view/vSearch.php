<?php
/**
 * MVP - View: vSearch
 * Form tìm kiếm. Nhận $isAdmin, $currentKeyword từ Presenter.
 */
$action  = isset($isAdmin) && $isAdmin ? 'admin.php' : 'index.php';
$keyword = isset($currentKeyword) ? htmlspecialchars($currentKeyword) : '';
?>
<h3>Tìm kiếm</h3>
<form method="get" action="<?= htmlspecialchars($action) ?>">
    <?php if (isset($isAdmin) && $isAdmin): ?>
        <input type="hidden" name="sanpham" value="1">
    <?php endif; ?>
    <input type="text" name="ten" placeholder="Nhập từ khóa tìm kiếm..." value="<?= $keyword ?>" required>
    <input type="submit" value="Tìm" name="btnTimkiem">
</form>
