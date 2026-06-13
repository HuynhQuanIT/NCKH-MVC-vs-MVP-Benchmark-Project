<?php
/**
 * MVP - Presenter: pType
 * Điều phối dữ liệu Type giữa Model và View.
 */
include_once("model/mType.php");

class pType {
    private $model;

    public function __construct() {
        $this->model = new mType();
    }

    public function getTypes(): array {
        $rs = $this->model->mListType();
        return $this->fetchAll($rs);
    }

    public function getTypeById(int $id): ?array {
        $rs = $this->model->mListTypeById($id);
        if ($rs && $rs->num_rows > 0) {
            return $rs->fetch_assoc();
        }
        return null;
    }

    public function updateType($idType, string $typeName): bool {
        return (bool)$this->model->mUpdateType($idType, $typeName);
    }

    public function deleteType($idType): bool {
        return (bool)$this->model->mDeleteType($idType);
    }

    private function fetchAll($rs): array {
        if (!$rs || $rs->num_rows == 0) return [];
        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
?>
