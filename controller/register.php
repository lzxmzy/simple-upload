<?php
// controller/register.php
// 注册控制器

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
$email = trim($_POST['email'] ?? '');

if (empty($username) || empty($password) || empty($email)) {
    echo json_encode(['code' => 400, 'msg' => '所有字段不能为空']);
    exit;
}

if (strlen($username) < 3 || strlen($username) > 20) {
    echo json_encode(['code' => 400, 'msg' => '用户名长度必须在3-20之间']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['code' => 400, 'msg' => '密码长度不能少于6位']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['code' => 400, 'msg' => '邮箱格式错误']);
    exit;
}

try {
    $userModel = new UserModel();

    if ($userModel->checkUsernameExists($username)) {
        echo json_encode(['code' => 400, 'msg' => '用户名已存在']);
        exit;
    }

    $userId = $userModel->register($username, $password, $email);

    echo json_encode([
        'code' => 200,
        'msg' => '注册成功，请登录',
        'data' => ['user_id' => $userId]
    ]);
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>