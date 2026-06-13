<?php
    class mKetNoi {
        public function moKetNoi(){
            // $conn = mysqli_connect("sql300.infinityfree.com","if0_41495697","SmtlAgkwGBVBfqC","if0_41495697_qlbh");
            $conn = mysqli_connect("localhost","root","","qlbh");
            mysqli_set_charset($conn, "utf8mb4");
            return $conn;
        }
        public function dongKetNoi($con){
            $con -> close();
        }
    }

?>

