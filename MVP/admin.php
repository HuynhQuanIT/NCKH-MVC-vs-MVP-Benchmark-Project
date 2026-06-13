<?php
/**
 * MVP - Entry Point: admin.php
 * Presenter xử lý toàn bộ logic admin, View chỉ hiển thị.
 */
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] == 3) {
    echo "<script>alert('Bạn không có quyền truy cập'); window.location.href='index.php';</script>";
    exit;
}

include_once("presenter/pProduct.php");
include_once("presenter/pType.php");
include_once("presenter/pUser.php");

$pProduct = new pProduct();
$pType    = new pType();
$pUser    = new pUser();

// --- Presenter xử lý tất cả POST actions ---

// Thêm sản phẩm
if (isset($_REQUEST['Them'])) {
    $ok = $pProduct->insertProduct(
        $_REQUEST['txtName'], $_REQUEST['txtPrice'],
        $_REQUEST['txtSalePrice'], $_FILES['fileImage'], $_REQUEST['txtType']
    );
    $msg = $ok ? 'Thêm sản phẩm thành công!' : 'Thêm sản phẩm thất bại!';
    $redirect = $ok ? 'admin.php?sanpham' : '#';
    echo "<script>alert('$msg'); window.location.href='$redirect';</script>";
    exit;
}

// Cập nhật sản phẩm
if (isset($_REQUEST['Sua']) && isset($_REQUEST['id']) && !isset($_REQUEST['suath'])) {
    $ok = $pProduct->updateProduct(
        (int)$_REQUEST['id'], $_REQUEST['txtName'],
        $_REQUEST['txtPrice'], $_REQUEST['txtSalePrice'],
        $_FILES['fileImage'], $_REQUEST['currentImage'] ?? '', $_REQUEST['txtType']
    );
    $msg = $ok ? 'Cập nhật sản phẩm thành công!' : 'Cập nhật sản phẩm không thành công!';
    echo "<script>alert('$msg'); window.location.href='admin.php?sanpham';</script>";
    exit;
}

// Xóa sản phẩm
if (isset($_REQUEST['xoasp']) && isset($_REQUEST['id'])) {
    $ok  = $pProduct->deleteProduct((int)$_REQUEST['id']);
    $msg = $ok ? 'Xóa sản phẩm thành công!' : 'Xóa sản phẩm không thành công!';
    echo "<script>alert('$msg'); window.location.href='admin.php?sanpham';</script>";
    exit;
}

// Cập nhật thương hiệu
if (isset($_REQUEST['Sua']) && isset($_REQUEST['suath'])) {
    $ok  = $pType->updateType((int)$_REQUEST['id'], $_REQUEST['txtName']);
    $msg = $ok ? 'Cập nhật thương hiệu thành công!' : 'Cập nhật thương hiệu không thành công!';
    echo "<script>alert('$msg'); window.location.href='admin.php?thuonghieu';</script>";
    exit;
}

// Xóa thương hiệu
if (isset($_REQUEST['xoath']) && isset($_REQUEST['id'])) {
    $ok  = $pType->deleteType((int)$_REQUEST['id']);
    $msg = $ok ? 'Xóa thương hiệu thành công!' : 'Xóa thương hiệu không thành công!';
    echo "<script>alert('$msg'); window.location.href='admin.php?thuonghieu';</script>";
    exit;
}

// Thêm người dùng
if (isset($_REQUEST['btnThem'])) {
    $ok  = $pUser->addUser($_REQUEST['txtusername'], $_REQUEST['txtpassword'], $_REQUEST['txtRole']);
    $msg = $ok ? 'Tạo người dùng thành công' : 'Tạo người dùng không thành công';
    echo "<script>alert('$msg'); window.location='admin.php?themnguoidung';</script>";
    exit;
}

// --- Presenter chuẩn bị dữ liệu cho từng màn hình ---
$isAdmin        = true;
$currentKeyword = $_GET['ten'] ?? '';

if (isset($_REQUEST['thuonghieu'])) {
    $types = $pType->getTypes();

} elseif (isset($_REQUEST['sanpham'])) {
    if (isset($_REQUEST['btnTimkiem']))
        $products = $pProduct->getProductsByName($_REQUEST['ten']);
    else
        $products = $pProduct->getAll();

} elseif (isset($_REQUEST['themsp'])) {
    $types = $pType->getTypes();   // cần cho dropdown

} elseif (isset($_REQUEST['nguoidung'])) {
    $users = $pUser->getUsers();

} elseif (isset($_REQUEST['themnguoidung'])) {
    $roles = $pUser->getRoles();

} elseif (isset($_REQUEST['suasp']) && isset($_REQUEST['id'])) {
    $product = $pProduct->getProductById((int)$_REQUEST['id']);
    $types   = $pType->getTypes();
    if (!$product) {
        echo "<script>alert('Mã sản phẩm không tồn tại'); window.location.href='admin.php';</script>";
        exit;
    }

} elseif (isset($_REQUEST['suath']) && isset($_REQUEST['id'])) {
    $typeData = $pType->getTypeById((int)$_REQUEST['id']);
    if (!$typeData) {
        echo "<script>alert('Mã thương hiệu không tồn tại'); window.location.href='admin.php';</script>";
        exit;
    }
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

    <div class="admin-top-bar">
        <a href="index.php">Trang chủ</a>
        <a href="index.php?logout" onclick="return confirm('Bạn muốn đăng xuất?')">Đăng xuất</a>
    </div>

    <?php include("view/vSearch.php"); ?>

    <table class="admin-layout">
        <tr>
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
                    <?php if ($_SESSION['role'] == 1): ?>
                    <h2>Người dùng</h2>
                    <ul>
                        <a href="admin.php?nguoidung">Xem danh sách người dùng</a>
                        <a href="admin.php?themnguoidung">Thêm người dùng</a>
                    </ul>
                    <?php endif; ?>
                </div>
            </td>
            <td class="admin-right">
                <?php if (isset($_REQUEST['thuonghieu'])): ?>
                    <h3>Danh sách thương hiệu</h3>
                    <?php include("view/vAdminTypes.php"); ?>

                <?php elseif (isset($_REQUEST['sanpham'])): ?>
                    <h3>Danh sách sản phẩm</h3>
                    <?php include("view/vAdminProducts.php"); ?>

                <?php elseif (isset($_REQUEST['themsp'])): ?>
                    <?php include("view/vAdminAddProduct.php"); ?>

                <?php elseif (isset($_REQUEST['nguoidung'])): ?>
                    <h3>Danh sách người dùng</h3>
                    <?php include("view/vAdminUser.php"); ?>

                <?php elseif (isset($_REQUEST['themnguoidung'])): ?>
                    <h3>Thêm người dùng</h3>
                    <?php include("view/vAdminAddUser.php"); ?>

                <?php elseif (isset($_REQUEST['suasp'])): ?>
                    <?php include("view/vUpdateProduct.php"); ?>

                <?php elseif (isset($_REQUEST['suath'])): ?>
                    <?php include("view/vUpdateType.php"); ?>

                <?php else: ?>
                    <h1>Chào mừng đến với trang Admin</h1>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <footer>
        <h2>Huỳnh Văn Quân - 22636731</h2>
    </footer>
</div>
</body>
</html>
