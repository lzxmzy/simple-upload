<?php
// controller/updatePwd.php
// 修改密码控制器

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/pdo_connect.php';
require_once '../model/UserModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 400, 'msg' => '请求方式错误']);
    exit;
}

$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($old_password) || empty($new_password)) {
    echo json_encode(['code' => 400, 'msg' => '参数不能为空']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['code' => 400, 'msg' => '新密码长度不能少于6位']);
    exit;
}

try {
    global $real_user_id;
    $userModel = new UserModel();

    // 验证旧密码
    $user = $userModel->getUserById($real_user_id);
    if (!password_verify($old_password, $user['password'])) {
        echo json_encode(['code' => 400, 'msg' => '旧密码错误']);
        exit;
    }

    $result = $userModel->updatePassword($real_user_id, $new_password);

    if ($result) {
        echo json_encode(['code' => 200, 'msg' => '密码修改成功']);
    } else {
        echo json_encode(['code' => 400, 'msg' => '修改失败']);
    }
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>