<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] == 3) {
    echo "<script>alert('Bạn không có quyền truy cập'); window.location.href='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản trị</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="admin-wrap">
    <img src="image/adminbanner.png" class="banner-img" alt="Admin Banner">

    <!-- Thanh top admin -->
    <div class="admin-top-bar">
        <a href="index.php">Trang chủ</a>
        <a href="index.php?logout" onclick="return confirm('Bạn muốn đăng xuất?')">Đăng xuất</a>
    </div>

    <?php include("view/search.php"); ?>

    <table class="admin-layout">
        <tr>
            <!-- Sidebar trái -->
            <td class="admin-left">
                <div class="admin-menu">
                    <h2>Sản phẩm</h2>
                    <ul>
                        <a href="admin.php?sanpham">Xem danh sách sản phẩm</a>
                        <a href="admin.php?themsp">Thêm sản phẩm</a>
                    </ul>

                    <h2>Thương hiệu</h2>
                    <ul>
                        <a href="admin.php?thuonghieu">Xem danh sách thương hiệu</a>
                    </ul>

                    <?php
                    if ($_SESSION['role'] == 1) {
                        echo '<h2>Người dùng</h2>';
                        echo '<ul>';
                        echo '<a href="admin.php?nguoidung">Xem danh sách người dùng</a>';
                        echo '<a href="admin.php?themnguoidung">Thêm người dùng</a>';
                        echo '</ul>';
                    }
                    ?>
                </div>
            </td>

            <!-- Nội dung phải -->
            <td class="admin-right">
                <?php
                if (isset($_REQUEST['thuonghieu'])) {
                    echo "<h3>Danh sách thương hiệu</h3>";
                    include("view/adminTypes.php");
                } elseif (isset($_REQUEST['sanpham'])) {
                    echo "<h3>Danh sách sản phẩm</h3>";
                    include("view/adminProducts.php");
                } elseif (isset($_REQUEST['themsp'])) {
                    include_once("view/adminAddSanPham.php");
                } elseif (isset($_REQUEST['nguoidung'])) {
                    echo "<h3>Danh sách người dùng</h3>";
                    include_once("view/adminUser.php");
                } elseif (isset($_REQUEST['themnguoidung'])) {
                    echo "<h3>Thêm người dùng</h3>";
                    include_once("view/adminAddUser.php");
                } elseif (isset($_REQUEST['suasp'])) {
                    include_once("view/updateProduct.php");
                } elseif (isset($_REQUEST['xoasp'])) {
                    include_once("view/deleteProduct.php");
                } elseif (isset($_REQUEST['suath'])) {
                    include_once("view/updateType.php");
                } elseif (isset($_REQUEST['xoath'])) {
                    include_once("view/deleteType.php");
                } else {
                    echo "<h1>Chào mừng đến với trang Admin</h1>";
                }
                ?>
            </td>
        </tr>
    </table>

    <footer>
        <h2>Nguyễn Hoàng Khánh Duy - 22653721</h2>
    </footer>
</div>

</body>
</html>
