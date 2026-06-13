<?php
/**
 * MVP - Presenter: pProduct
 */
include_once("model/mProduct.php");
include_once("presenter/pUpload.php");

class pProduct {
    private $model;

    public function __construct() {
        $this->model = new mProduct();
    }

    public function getAll(): array {
        return $this->fetchAll($this->model->mListProduct());
    }

    public function getProductsByType(int $id): array {
        return $this->fetchAll($this->model->mListProductByType($id));
    }

    public function getProductsByName(string $name): array {
        return $this->fetchAll($this->model->mListProductByTen($name));
    }

    public function getProductById(int $id): ?array {
        $rs = $this->model->mListProductById($id);
        if ($rs && $rs->num_rows > 0) return $rs->fetch_assoc();
        return null;
    }

    public function insertProduct(string $name, $price, $salePrice, array $fileImage, $idType): bool {
        $image = '';
        if ($fileImage["tmp_name"] != "") {
            $u = new pUpload();
            if (!$u->uploadImage($fileImage, $name, $image)) return false;
        }
        return (bool)$this->model->mInsertProduct($name, $price, $salePrice, $image, $idType);
    }

    public function updateProduct($id, string $name, $price, $salePrice, array $fileImage, string $curImage, $idType): bool {
        $image = $curImage;
        if ($fileImage["tmp_name"] != "") {
            $u = new pUpload();
            if (!$u->uploadImage($fileImage, $name, $image)) return false;
        }
        return (bool)$this->model->mUpdateProduct($id, $name, $price, $salePrice, $image, $idType);
    }

    public function deleteProduct($id): bool {
        return (bool)$this->model->mDeleteProduct($id);
    }

    private function fetchAll($rs): array {
        if (!$rs || $rs->num_rows == 0) return [];
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
?>
