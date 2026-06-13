<form action="#" method="post">
    Username: <input type="text" name ="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name ="txtpassword" placeholder="Password" required> <br>
    <input type="submit" name="btnsubmit" value="Đăng ký">
</form>

<?php
if (isset($_REQUEST['btnsubmit'])) {
    include_once("controller/cUser.php");
    $p = new cUser();
    $ketqua = $p->cRegister($_REQUEST["txtusername"], $_REQUEST["txtpassword"]);
    if ($ketqua) {
        echo "<script>alert('Đăng ký thành công'); window.location='index.php?login';</script>";
    }
    else {
        echo "<script>alert('Đăng ký không thành công')</script>";
    }


}
?>