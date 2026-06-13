<?php
/**
 * MVP - Presenter: pUpload
 * Xử lý upload ảnh, không phụ thuộc View hay Model
 */
class pUpload {
    public function uploadImage($file, $fileName, &$image) {
        if (!$this->checkSize($file["size"])) return false;
        if (!$this->checkType($file["type"])) return false;

        $ext = "." . pathinfo($file["name"], PATHINFO_EXTENSION);
        $image = $this->normalizeName($fileName) . $ext;
        $dest = "image/" . $image;

        return move_uploaded_file($file["tmp_name"], $dest);
    }

    private function checkSize($size) {
        if ($size > 2 * 1024 * 1024) {
            echo "<script>alert('Kích thước tệp vượt quá giới hạn 2MB!');</script>";
            return false;
        }
        return true;
    }

    private function checkType($type) {
        $allowed = ["image/png", "image/jpeg", "image/jpg"];
        if (!in_array($type, $allowed)) {
            echo "<script>alert('Định dạng tệp không hợp lệ! Chỉ chấp nhận PNG, JPEG, JPG.');</script>";
            return false;
        }
        return true;
    }

    public function normalizeName($name) {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|A|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ|D|Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|E|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị|I|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|O|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|U|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Y|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $plain => $pattern) {
            $name = preg_replace("/($pattern)/i", $plain, $name);
        }
        $name = strtolower($name);
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^a-z0-9.\-_]/', '', $name);
        return $name;
    }
}
?>
