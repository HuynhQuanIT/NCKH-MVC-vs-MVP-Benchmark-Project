<?php
/**
 * MVP - Entry Point: index.php
 * Presenter điều phối: nhận Request → gọi Model → chuẩn bị data → render View.
 * View KHÔNG được gọi Presenter/Model trực tiếp.
 */
session_start();

include_once("presenter/pProduct.php");
include_once("presenter/pType.php");
include_once("presenter/pUser.php");

// --- Xử lý Actions (Presenter xử lý trước khi render) ---
$loginError    = false;
$registerError = false;

if (isset($_REQUEST['logout'])) {
    session_destroy();
    echo "<script>alert('Bạn đã đăng xuất thành công'); window.location.href='index.php';</script>";
    exit;
}

if (isset($_REQUEST['btnsubmit'])) {
    $pUser = new pUser();
    if (isset($_REQUEST['login'])) {
        // Presenter xử lý login
        $ok = $pUser->login($_REQUEST['txtusername'], $_REQUEST['txtpassword']);
        if ($ok) {
            echo "<script>alert('Đăng nhập thành công'); window.location.href='admin.php';</script>";
            exit;
        }
        $loginError = true;
    } elseif (isset($_REQUEST['register'])) {
        // Presenter xử lý register
        $ok = $pUser->register($_REQUEST['txtusername'], $_REQUEST['txtpassword']);
        if ($ok) {
            echo "<script>alert('Đăng ký thành công'); window.location='index.php?login';</script>";
            exit;
        }
        $registerError = true;
    }
}

// --- Presenter chuẩn bị dữ liệu cho View ---
$pType    = new pType();
$types    = $pType->getTypes();       // Sidebar danh mục

$pProduct = new pProduct();
if (isset($_REQUEST['idType'])) {
    $products = $pProduct->getProductsByType((int)$_REQUEST['idType']);
} elseif (isset($_REQUEST['btnTimkiem'])) {
    $products = $pProduct->getProductsByName($_REQUEST['ten']);
} else {
    $products = $pProduct->getAll();
}

// Search view vars
$isAdmin        = false;
$currentKeyword = $_GET['ten'] ?? '';
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
    <?php if (!isset($_SESSION['login'])): ?>
        <a href="index.php?login">Đăng nhập</a>
        <a href="index.php?register">Đăng ký</a>
    <?php else: ?>
        <a href="admin.php">Admin</a>
        <a href="index.php?logout" onclick="return confirm('Bạn muốn đăng xuất?')">Đăng xuất</a>
    <?php endif; ?>
</div>

<table class="layout-table">
    <tr>
        <td id="left">
            <div class="sidebar">
                <h2>Danh mục</h2>
                <?php include("view/vListType.php"); ?>
            </div>
        </td>
        <td id="right">
            <?php if (isset($_REQUEST['login'])): ?>
                <h2>Đăng nhập</h2>
                <?php include("view/vLogin.php"); ?>
            <?php elseif (isset($_REQUEST['register'])): ?>
                <h2>Đăng ký</h2>
                <?php include("view/vRegister.php"); ?>
            <?php else: ?>
                <?php include("view/vSearch.php"); ?>
                <h2>Danh sách sản phẩm</h2>
                <?php include("view/vListProduct.php"); ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

<footer>
    <h2>Huỳnh Văn Quân - 22636731</h2>
</footer>
</body>
</html>
