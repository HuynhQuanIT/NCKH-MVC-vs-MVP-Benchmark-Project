<?php
include_once("model/mProduct.php");
include_once("controller/cUpload.php");

class cProduct{
    public function cListProduct(){
        $p = new mProduct();
        $rs = $p -> mListProduct();
        if($rs -> num_rows>0){
            return $rs;
        }
            return false;
    }

    public function cListProductByType($id){
        $p = new mProduct();
        $rs = $p -> mListProductByType($id);
        if($rs -> num_rows>0){
            return $rs;
        }
            return false;
    }

        public function cListProductByTen($ten){
        $p = new mProduct();
        $rs = $p -> mListProductByTen($ten);
        if($rs -> num_rows>0){
            return $rs;
        }
            return false;
    }

        public function cListProductById($id){
            $p = new mProduct();
            $rs = $p -> mListProductById($id);
            if($rs -> num_rows>0){
                return $rs;
            }
                return false;
        }

    public function cInsertProduct($name, $price, $salePrice, $fileImage, $idType){
        if($fileImage["tmp_name"] != ""){
            $p = new cUpLoad();
                $result = $p->cUpLoadAnh($fileImage, $name, $image);
                if(!$result){
                    return false;
                }
            }
            $p = new mProduct();
            $result = $p->mInsertProduct($name, $price, $salePrice, $image, $idType);
            if(!$result){
                echo"Thêm sản phẩm thất bại";
            }
            return $result; 
        }


    public function cUpdateProduct($idProduct,$name, $price, $salePrice, $fileImage,$image, $idType){
            if($fileImage["tmp_name"] != ""){
                $p = new cUpLoad();
                $result = $p->cUpLoadAnh($fileImage, $name, $image);
                if(!$result){
                    return false;   
                }
            }
            $p = new mProduct();
            $result = $p->mUpdateProduct($idProduct,$name, $price, $salePrice, $image, $idType);
            if(!$result){
                echo"Thêm sản phẩm thất bại";
            }
            return $result; 
        }
     public function cDeleteProduct($idProduct){
        $p = new mProduct();
        $rs = $p -> mDeleteProduct($idProduct);
        if($rs){
            return $rs;
        }
            return false;
    }
}
?>