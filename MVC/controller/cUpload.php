<!-- $file = array(
    'name' => 'hinhanh.jpg',        // Tên file gốc trên máy người dùng
    'type' => 'image/jpeg',         // Kiểu MIME
    'tmp_name' => '/tmp/php123.tmp',// File tạm trên server
    'error' => 0,                   // Mã lỗi upload (0 nghĩa là không lỗi)
    'size' => 52341                 // Kích thước file (bytes)
); -->
<?php
    class cUpLoad{
        public function cUpLoadAnh($file, $fileName, & $image){
            $size = $file["size"];
            $type = $file["type"];
            $tmp_name = $file["tmp_name"];
            if (!$this->chkSize($size)) {
                return false;   
            }
            if (!$this->chkType($type)) {
                return false;
            }
            $folder = "image/";
            
            // Sử dụng pathinfo để lấy phần mở rộng
            $fileInfo = pathinfo($file["name"]);
            $ext = "." . $fileInfo['extension'];
            
            $image = $this->changeName($fileName) . $ext;
            $des = $folder.$image;
            if (move_uploaded_file($tmp_name, $des)) {
                return true;
            } else {
                return false;
            }
        }
        private function chkSize($size){
            if ($size > 2 * 1024 * 1024) { // Giới hạn 2MB
                echo "<script>alert('Kích thước tệp vượt quá giới hạn 2MB!');</script>";
                return false;
            } else {
                return true;
            }
        }
        private function chkType($type){
            $arrayType = array("image/png", "image/jpeg", "image/jpg");
            if (in_array($type, $arrayType)) {
                return true;
            } else {
                echo "<script>alert('Định dạng tệp không hợp lệ! Chỉ chấp nhận PNG, JPEG, JPG.');</script>";
                return false;
            }
        }
        public function changeName($name){
            $unicode = array(
                'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|A|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'd'=>'đ|D|Đ',
                'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|E|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'i'=>'í|ì|ỉ|ĩ|ị|I|Í|Ì|Ỉ|Ĩ|Ị',
                'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|O|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|U|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Y|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            );

            // Thay thế ký tự Unicode => bỏ dấu tiếng Việt
            foreach($unicode as $nonUnicode => $uni){
                $name = preg_replace("/($uni)/i", $nonUnicode, $name);
            }

            // Chuyển đổi các ký tự đặc biệt thành ký tự thường
            $name = strtolower($name);

            // Thay thế khoảng trắng thành dấu -
            $name = str_replace(' ', '-', $name);
            
            // Loại bỏ các ký tự không hợp lệ
            $name = preg_replace('/[^a-z0-9\.\-_]/', '', $name);
            return $name;
        }
    }
?>