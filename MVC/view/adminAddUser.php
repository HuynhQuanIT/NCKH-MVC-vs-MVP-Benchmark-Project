<?php
include_once("controller/cUser.php")
?>

<form action="" method="POST">
    Username: <input type="text" name ="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name ="txtpassword" placeholder="Password" required> <br>
    Role  <select name="txtRole" id="txtRole">
<?php
    $p = new cUser();
    $ketqua = $p->cListRole();
    if($ketqua){
        while($row = $ketqua -> fetch_assoc()){
            echo "<option value='
            ".$row['idRole']."
            '>
            ".$row['nameRole']."
            </option>";
        }
                }
?>
</select> <br>
    <input type="submit" name="btnThem" value="Tạo người dùng">
</form>
<?php
    if(isset($_REQUEST['btnThem'])){
        include_once("controller/cUser.php");
        $p = new cUser();
        $ketqua = $p->cAddUser($_REQUEST["txtusername"], $_REQUEST["txtpassword"], $_REQUEST["txtRole"]);
        if($ketqua==true)
        {
            echo "<script>alert('Tạo người dùng thành công'); window.location='admin.php?themnguoidung';</script>";
        }else{
            echo "<script>alert('Tạo người dùng không thành công')</script>";
        }
        
        
    }
?>