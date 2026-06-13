<?php
include_once("model/mUser.php");
class cUser {
    public function cLogin($user,$pass){
        
        $p = new mUser();
        $listUser = $p->mLogin($user,md5($pass));
        if($listUser->num_rows >0)
        {
            $_SESSION['login'] = true;
            $_SESSION['role'] = $listUser->fetch_array()["idRole"];
            return true;
        }
        else
        {
            return false;
        }

    }

    public function cRegister($user,$pass){
        $p = new mUser();
        $check = $p ->checkID($user);
        if($check->num_rows >0)
        {
            echo "<script>alert('Username đã tồn tại')</script>";
            return false;
        }
        else
        {
            $ketqua = $p->mRegister($user,md5($pass));
            return $ketqua;
        }
    }

    public function cListUser(){
        $p = new mUser();
        $ketqua = $p->mListUser();
        if($ketqua->num_rows > 0){
            return $ketqua; 
        }
            return false;
    }

      public function cListRole(){
        $p = new mUser();
        $ketqua = $p->mListRole();
        if($ketqua->num_rows > 0){
            return $ketqua; 
        }
            return false;
    }

    public function cAddUser($user,$pass,$r){
        $p = new mUser();
        $check = $p ->checkID($user);
        if($check->num_rows >0)
        {
            echo "<script>alert('Username đã tồn tại')</script>";
            return false;
        }
        else
        {
            $ketqua = $p->mAddUser($user,md5($pass),$r);
            return $ketqua;
        }
    }

    
}
?>