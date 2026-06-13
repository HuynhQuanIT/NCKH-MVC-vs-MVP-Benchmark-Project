<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Cửa hàng</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>


<img src="image/banner.jpg" class="banner-img" alt="Banner">


<div class="top-nav">
    <a href="index.php">Trang chủ</a>
    <?php
    if (!isset($_SESSION['login'])) {
        echo '<a href="index.php?login">Đăng nhập</a>';
        echo '<a href="index.php?register">Đăng ký</a>';
    } else {
        echo '<a href="admin.php">Admin</a>';
        echo '<a href="index.php?logout" onclick="return confirm(\'Bạn muốn đăng xuất?\')">Đăng xuất</a>';
    }
    ?>
</div>

<table class="layout-table">
    <tr>
    
        <td id="left">
            <div class="sidebar">
                <h2>Danh mục</h2>
                <?php include("view/listType.php"); ?>
            </div>
        </td>

        <!-- Nội dung phải -->
        <td id="right">
            <?php
            if (isset($_REQUEST['login'])) {
                echo '<h2>Đăng nhập</h2>';
                include("view/login.php");
            } elseif (isset($_REQUEST['register'])) {
                echo '<h2>Đăng ký</h2>';
                include("view/register.php");
            } elseif (isset($_REQUEST['logout'])) {
                include("view/logout.php");
            } else {
                include("view/search.php");
                echo "<h2>Danh sách sản phẩm</h2>";
                include("view/listProduct.php");
            }
            ?>
        </td>
    </tr>
</table>

<footer>
    <h2>Nguyễn Hoàng Khánh Duy - 22653721</h2>
</footer>

</body>
</html>