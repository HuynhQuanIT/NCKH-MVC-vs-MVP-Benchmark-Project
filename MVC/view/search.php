
<?php

$isAdmin = false;
if (isset($_SERVER['SCRIPT_NAME'])) {
    $isAdmin = (basename($_SERVER['SCRIPT_NAME']) === 'admin.php');
}

$action = $isAdmin ? 'admin.php' : 'index.php';
$currentKeyword = isset($_GET['ten']) ? $_GET['ten'] : '';
?>

<h3>Tìm kiếm</h3>
<form method="get" action="<?php echo htmlspecialchars($action); ?>">
    <?php if ($isAdmin) { ?>
        <input type="hidden" name="sanpham" value="1">
    <?php
}?>
    <input type="text" name="ten" placeholder="Nhập từ khóa tìm kiếm..." value="<?php echo htmlspecialchars($currentKeyword); ?>" required>
    <input type="submit" value="Tìm" name="btnTimkiem">
</form>

    