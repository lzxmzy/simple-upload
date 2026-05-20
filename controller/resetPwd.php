<?php
// controller/resetPwd.php
// 重置密码控制器（仅超级管理员可用）

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/check_role.php';
require_once '../config/pdo_connect.php';
require_once '../model/UserModel.php';

header('Content-Type: application/json');

// 权限检测：仅超级管理员可重置密码
if (!is_super_admin()) {
    echo json_encode(['code' => 403, 'msg' => '无权限执行此操作']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 400, 'msg' => '请求方式错误']);
    exit;
}

$user_id = intval($_POST['user_id'] ?? 0);
$new_password = $_POST['new_password'] ?? '';

if (empty($user_id) || empty($new_password)) {
    echo json_encode(['code' => 400, 'msg' => '参数不能为空']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['code' => 400, 'msg' => '密码长度不能少于6位']);
    exit;
}

try {
    $userModel = new UserModel();
    $result = $userModel->resetPassword($user_id, $new_password);

    if ($result) {
        echo json_encode(['code' => 200, 'msg' => '密码重置成功']);
    } else {
        echo json_encode(['code' => 400, 'msg' => '用户不存在']);
    }
} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>