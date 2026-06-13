<?php
include_once("mKetNoi.php");

class mProduct {
    public function mListProduct() {
        $p = new mKetNoi();
        $con = $p->moKetNoi();
        if($con){
	        $rs = $con->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType");
            return $rs;
        }
            return false;
        $p->dongKetNoi($con);

    }

      public function mListProductByType($id) {
        $p = new mKetNoi();
        $con = $p->moKetNoi();
        if($con){
	        $rs = $con->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType where t.idType='$id'");
            return $rs;
        }
            return false;
        $p->dongKetNoi($con);
    }

       public function mListProductByTen($ten) {
        $p = new mKetNoi();
        $con = $p->moKetNoi();
        if($con){
	        $rs = $con->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType where productName like N'%$ten%'");
            return $rs;
        }
            return false;
        $p->dongKetNoi($con);
    }
        public function mListProductById($id) {
        $p = new mKetNoi();
        $con = $p->moKetNoi();
        if($con){
	        $rs = $con->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType where p.idProduct = '$id'");
            return $rs;
        }
            return false;
        $p->dongKetNoi($con);
    }


    public function getID($id){
        $p = new mKetNoi();
        $con = $p->moKetNoi();
        if($con){
            $rs = $con->query("select * from products where idType = '$id'");
            return $rs;
        }
            return false;
        $p->dongKetNoi($con);

    }

    public function mInsertProduct($name,$price,$salePrice,$image,$idType){ 
            $p = new mKetNoi();
            $con = $p->moKetNoi();
            if($con){
                $rs = $con->query("INSERT INTO products (productName, productPrice, salePrice, image, idType) VALUES (N'$name', '$price', '$salePrice', '$image', '$idType')");
                return $rs;
            }
                return false;
            $p->dongKetNoi($con);
        }
    public function mUpdateProduct($idProduct,$name,$price,$salePrice,$image,$idType){ 
            $p = new mKetNoi();
            $con = $p->moKetNoi();
            $result = $con->query("UPDATE products SET productName = '$name', productPrice = '$price', salePrice = '$salePrice',image = '$image', idType = '$idType' WHERE idProduct = $idProduct ");
            $p->dongKetNoi($con);
            return $result;
        }
    public function mDeleteProduct($idProduct){ 
            $p = new mKetNoi();
            $con = $p->moKetNoi();
            $result = $con->query("DELETE FROM products where idProduct = '$idProduct'");
            $p->dongKetNoi($con);
            return $result;
        }
    
}   