<?php
include_once("model/mType.php");

class cType{
    public function cListType(){
        $p = new mType();
        $listtype = $p -> mListType();
        if($listtype -> num_rows >0)
        {
            return $listtype;
        }
            return false;
    }
    public function cListTypeById($idType){
        $p = new mType();
        $listtype = $p -> mListTypeById($idType);
        if($listtype -> num_rows >0)
        {
            return $listtype;
        }
            return false;
    }
    public function cUpdateType($idType,$typeName){
        $p = new mType();
        $listtype = $p -> mUpdateType($idType,$typeName);
        if($listtype)
        {
            return $listtype;
        }
            return false;
    }
    public function cDeleteType($idType){
        $p = new mType();
        $listtype = $p -> mDeleteType($idType);
        if($listtype)
        {
            return $listtype;
        }
            return false;
    }
}
?>