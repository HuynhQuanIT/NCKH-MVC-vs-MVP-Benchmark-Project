<?php
 include_once("mKetNoi.php");
    class mUser{
        public function mLogin($id,$pws){
           
            $p = new mKetnoi();
            $con = $p-> moKetNoi();
            if($con) 
            {
                $query = "select * from user where username = '$id' and password ='$pws'";
                $ketqua = $con->query($query);
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }

        public function mRegister($id,$pws){
            $p = new mKetnoi();
            $con = $p-> moKetNoi();
            if($con) 
            {
                $query = "insert into user(username,password,idRole) values ('$id','$pws','3')";
                $ketqua = $con->query($query);
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }

        public function checkID($id){
            $p = new mKetnoi();
            $con = $p-> moKetNoi();
            if($con) 
            {
                $query = "select * from user where username = '$id'";
                $ketqua = $con->query($query);
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }

        public function mListUser(){
            $p = new mKetNoi();
            $con = $p->moKetNoi();
            if($con)
            {
                $ketqua = $con ->query( "select u.id, u.username, r.nameRole  from user u , role r where u.idRole = r.idRole ");
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }
          public function mListRole(){
            $p = new mKetNoi();
            $con = $p->moKetNoi();
            if($con)
            {
                $ketqua = $con ->query( "select * from role");
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }

        public function mAddUser($id,$pws,$r){
            $p = new mKetnoi();
            $con = $p-> moKetNoi();
            if($con) 
            {
                $query = "insert into user(username,password,idRole) values ('$id','$pws','$r')";
                $ketqua = $con->query($query);
                return $ketqua;
            }
            $p->dongKetNoi($con);
        }
    }
?>