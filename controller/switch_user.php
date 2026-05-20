<?php
// controller/switch_user.php
// 视图切换控制器

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/check_role.php';
require_once '../config/pdo_connect.php';
require_once '../model/UserModel.php';

header('Content-Type: application/json');

// 权限检测：仅管理员可切换视图
if (!is_admin()) {
    echo json_encode(['code' => 403, 'msg' => '无权限执行此操作']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 400, 'msg' => '请求方式错误']);
    exit;
}

$user_id = intval($_POST['user_id'] ?? 0);

if (empty($user_id)) {
    // 切换回自己
    $_SESSION['switch_user_id'] = $_SESSION['real_user_id'];
    echo json_encode(['code' => 200, 'msg' => '已切换回自己的视图']);
    exit;
}

try {
    $userModel = new UserModel();
    $user = $userModel->getUserById($user_id);

    if (!$user) {
        echo json_encode(['code' => 404, 'msg' => '用户不存在']);
        exit;
    }

    // 修改switch_user_id，real_user_id保持不变
    $_SESSION['switch_user_id'] = $user_id;

    echo json_encode([
        'code' => 200,
        'msg' => '已切换到用户: ' . $user['username'] . ' 的视图',
        'data' => ['username' => $user['username']]
    ]);

} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>