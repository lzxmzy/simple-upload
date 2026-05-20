<?php
// controller/MsgController.php
// 留言控制器

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/pdo_connect.php';
require_once '../model/MsgModel.php';
require_once '../config/check_role.php';

header('Content-Type: application/json');

$msgModel = new MsgModel();
global $real_user_id, $switch_user_id, $real_user_role;

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // 获取留言列表
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);

        // 根据switch_user_id查询，管理员可以查看所有
        if (is_admin()) {
            // 管理员可以根据switch_user_id查看指定用户的留言
            $list = $msgModel->getMsgListByUser($switch_user_id, $page, $limit);
            $total = $msgModel->getMsgCountByUser($switch_user_id);
        } else {
            // 普通用户只能看自己的
            $list = $msgModel->getMsgListByUser($real_user_id, $page, $limit);
            $total = $msgModel->getMsgCountByUser($real_user_id);
        }

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

    } elseif ($method === 'POST' && !isset($_POST['action'])) {
        // 添加留言
        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            echo json_encode(['code' => 400, 'msg' => '留言内容不能为空']);
            exit;
        }

        // 使用real_user_id添加，确保是当前登录用户
        $id = $msgModel->addMsg($real_user_id, $content);

        echo json_encode([
            'code' => 200,
            'msg' => '添加成功',
            'data' => ['id' => $id]
        ]);

    } elseif ($method === 'DELETE') {
        // 单条删除
        parse_str(file_get_contents('php://input'), $data);
        $id = intval($data['id'] ?? 0);

        if (empty($id)) {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
            exit;
        }

        // 权限检测：管理员可以删任何，普通用户只能删自己的
        $msg = $msgModel->getMsgById($id);
        if (!$msg) {
            echo json_encode(['code' => 404, 'msg' => '留言不存在']);
            exit;
        }

        if (!is_admin() && $msg['user_id'] != $real_user_id) {
            echo json_encode(['code' => 403, 'msg' => '无权限删除此留言']);
            exit;
        }

        $result = $msgModel->deleteMsg($id);

        echo json_encode([
            'code' => 200,
            'msg' => $result ? '删除成功' : '删除失败'
        ]);

    } elseif ($method === 'POST' && $_POST['action'] === 'batch_delete') {
        // 批量删除
        $ids = json_decode($_POST['ids'] ?? '[]', true);

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
            exit;
        }

        // 权限检测
        if (!is_admin()) {
            // 普通用户只能删除自己的留言
            foreach ($ids as $id) {
                $msg = $msgModel->getMsgById($id);
                if ($msg && $msg['user_id'] != $real_user_id) {
                    echo json_encode(['code' => 403, 'msg' => '无权限删除部分留言']);
                    exit;
                }
            }
        }

        $result = $msgModel->batchDeleteMsg($ids);

        echo json_encode([
            'code' => 200,
            'msg' => '批量删除成功',
            'data' => ['count' => $result]
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
?>