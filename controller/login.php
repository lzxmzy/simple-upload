<?php
// controller/login.php
// 登录控制器

require_once '../config/session_config.php';
require_once '../config/pdo_connect.php';
require_once '../model/UserModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 400, 'msg' => '请求方式错误']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['code' => 400, 'msg' => '用户名和密码不能为空']);
    exit;
}

try {
    $userModel = new UserModel();
    $user = $userModel->login($username, $password);

    if ($user) {
        // 登录成功，初始化Session
        $_SESSION['real_user_id'] = $user['id'];
        $_SESSION['switch_user_id'] = $user['id'];
        $_SESSION['real_user_role'] = $user['role'];
        $_SESSION['real_user_name'] = $user['username'];

        echo json_encode([
            'code' => 200,
            'msg' => '登录成功',
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['code' => 401, 'msg' => '用户名或密码错误']);
    }
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>