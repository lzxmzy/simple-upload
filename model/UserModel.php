<?php
// model/UserModel.php
// 用户模型，处理用户相关的数据库操作

require_once __DIR__ . '/../config/pdo_connect.php';

class UserModel {
    private $pdo;

    public function __construct() {
        $this->pdo = get_pdo();
    }

    /**
     * 根据ID获取用户信息
     * @param int $id
     * @return array|null
     */
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id, username, email, role, password, create_time FROM user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * 根据用户名获取用户信息
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT id, username, email, role, password, create_time FROM user WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function login($username, $password) {
        $user = $this->getUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * 用户注册
     * @param string $username
     * @param string $password
     * @param string $email
     * @return int 新用户ID
     */
    public function register($username, $password, $email) {
        $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO user (username, password, email, role) VALUES (?, ?, ?, 1)");
        $stmt->execute([$username, $hashed_pwd, $email]);
        return $this->pdo->lastInsertId();
    }

    /**
     * 检测用户名是否已存在
     * @param string $username
     * @return bool
     */
    public function checkUsernameExists($username) {
        $stmt = $this->pdo->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }

    /**
     * 重置用户密码
     * @param int $user_id
     * @param string $new_password
     * @return bool
     */
    public function resetPassword($user_id, $new_password) {
        $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed_pwd, $user_id]);
    }

    /**
     * 修改用户密码
     * @param int $user_id
     * @param string $new_password
     * @return bool
     */
    public function updatePassword($user_id, $new_password) {
        return $this->resetPassword($user_id, $new_password);
    }

    /**
     * 获取用户列表（分页）
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUserList($page, $limit) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, role, create_time 
            FROM user 
            ORDER BY id DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 获取用户总数
     * @return int
     */
    public function getUserCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM user");
        return $stmt->fetch()['count'];
    }

    /**
     * 删除用户
     * @param int $id
     * @return bool
     */
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM user WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 批量删除用户
     * @param array $ids
     * @return int 删除的行数
     */
    public function batchDeleteUser($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM user WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }
}
?>