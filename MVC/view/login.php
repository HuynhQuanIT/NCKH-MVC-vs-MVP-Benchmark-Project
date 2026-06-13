<form action="#" method="post">
    Username: <input type="text" name ="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name ="txtpassword" placeholder="Password" required> <br>
    <input type="submit" name="btnsubmit" value="Đăng nhập">
</form>

<?php
if (isset($_REQUEST['btnsubmit'])) {
    include_once("controller/cUser.php");
    $p = new cUser();
    $ketqua = $p->cLogin($_REQUEST["txtusername"], $_REQUEST["txtpassword"]);
    if ($ketqua) {
        echo "<script>alert('Đăng nhập thành công'); window.location.href='admin.php';</script>";
    }
    else {
        echo "<script>alert('Đăng nhập không thành công')</script>";
    }


}
?>