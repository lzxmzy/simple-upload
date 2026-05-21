<?php
// controller/AdminUserController.php
// 后台用户管理控制器

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/check_role.php';
require_once '../config/pdo_connect.php';
require_once '../model/UserModel.php';

header('Content-Type: application/json');

// 权限检测：仅超级管理员可管理用户
if (!is_super_admin()) {
    echo json_encode(['code' => 403, 'msg' => '无权限执行此操作']);
    exit;
}

$userModel = new UserModel();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // 获取用户列表
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);

        $list = $userModel->getUserList($page, $limit);
        $total = $userModel->getUserCount();

        echo json_encode([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);

    } elseif ($method === 'DELETE') {
        // 单条删除用户
        parse_str(file_get_contents('php://input'), $data);
        $id = intval($data['id'] ?? 0);

        if (empty($id)) {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
            exit;
        }

        // 不能删除自己
        global $real_user_id;
        if ($id == $real_user_id) {
            echo json_encode(['code' => 400, 'msg' => '不能删除自己']);
            exit;
        }

        // 不能删除超级管理员
        $user = $userModel->getUserById($id);
        if ($user && $user['role'] == 3) {
            echo json_encode(['code' => 400, 'msg' => '不能删除超级管理员']);
            exit;
        }

        $result = $userModel->deleteUser($id);

        echo json_encode([
            'code' => 200,
            'msg' => $result ? '删除成功' : '删除失败'
        ]);

    } elseif ($method === 'POST' && $_POST['action'] === 'batch_delete') {
        // 批量删除用户
        $ids = json_decode($_POST['ids'] ?? '[]', true);

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
            exit;
        }

        global $real_user_id;

        foreach ($ids as $id) {
            // 不能删除自己
            if ($id == $real_user_id) {
                echo json_encode(['code' => 400, 'msg' => '不能删除自己']);
                exit;
            }

            // 不能删除超级管理员
            $user = $userModel->getUserById($id);
            if ($user && $user['role'] == 3) {
                echo json_encode(['code' => 400, 'msg' => '不能删除超级管理员']);
                exit;
            }
        }

        $result = $userModel->batchDeleteUser($ids);

        echo json_encode([
            'code' => 200,
            'msg' => '批量删除成功',
            'data' => ['count' => $result]
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
