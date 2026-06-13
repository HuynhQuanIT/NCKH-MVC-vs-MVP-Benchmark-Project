<?php
include_once("mKetNoi.php");

class mType {
    public function mListType(){
        $p = new mKetnoi();
        $con = $p->moKetNoi();
        if($con){
            $ketqua = $con->query("select * from type");
            return $ketqua;
        }
            return false;
        $p->dongKetNoi($con);
    }
     public function mListTypeById($idType){
        $p = new mKetnoi();
        $con = $p->moKetNoi();
        if($con){
            $ketqua = $con->query("select * from type where idType = '$idType'");
            return $ketqua;
        }
            return false;
        $p->dongKetNoi($con);
    }
    public function mUpdateType($idType,$typeName){
        $p = new mKetnoi();
        $con = $p->moKetNoi();
        if($con){
            $ketqua = $con->query("update type set typeName ='$typeName' where idType ='$idType' ");
            return $ketqua;
        }
            return false;
        $p->dongKetNoi($con);
    }
     public function mDeleteType($idType){
        $p = new mKetnoi();
        $con = $p->moKetNoi();
        if($con){
            $ketqua = $con->query("Delete from type where idType = '$idType'  ");
            return $ketqua;
        }
            return false;
        $p->dongKetNoi($con);
    }
}
?>