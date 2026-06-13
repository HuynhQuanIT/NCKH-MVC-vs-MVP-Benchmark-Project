<?php
/**
 * MVP - Presenter: pUser
 * Điều phối dữ liệu User/Auth giữa Model và View.
 */
include_once("model/mUser.php");

class pUser {
    private $model;

    public function __construct() {
        $this->model = new mUser();
    }

    public function login(string $username, string $password): bool {
        $rs = $this->model->mLogin($username, md5($password));
        if ($rs && $rs->num_rows > 0) {
            $user = $rs->fetch_array();
            $_SESSION['login'] = true;
            $_SESSION['role']  = $user["idRole"];
            return true;
        }
        return false;
    }

    public function register(string $username, string $password): bool {
        $check = $this->model->checkID($username);
        if ($check && $check->num_rows > 0) {
            echo "<script>alert('Username đã tồn tại')</script>";
            return false;
        }
        return (bool)$this->model->mRegister($username, md5($password));
    }

    public function getUsers(): array {
        $rs = $this->model->mListUser();
        return $this->fetchAll($rs);
    }

    public function getRoles(): array {
        $rs = $this->model->mListRole();
        return $this->fetchAll($rs);
    }

    public function addUser(string $username, string $password, $role): bool {
        $check = $this->model->checkID($username);
        if ($check && $check->num_rows > 0) {
            echo "<script>alert('Username đã tồn tại')</script>";
            return false;
        }
        return (bool)$this->model->mAddUser($username, md5($password), $role);
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
