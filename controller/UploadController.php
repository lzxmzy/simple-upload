<?php
// controller/UploadController.php
// 文件上传控制器

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/pdo_connect.php';
require_once '../model/UploadModel.php';
require_once '../config/check_role.php';

header('Content-Type: application/json');

$uploadModel = new UploadModel();
global $real_user_id, $switch_user_id, $real_user_role;

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // 获取文件列表
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);

        if (is_admin()) {
            $list = $uploadModel->getFileListByUser($switch_user_id, $page, $limit);
            $total = $uploadModel->getFileCountByUser($switch_user_id);
        } else {
            $list = $uploadModel->getFileListByUser($real_user_id, $page, $limit);
            $total = $uploadModel->getFileCountByUser($real_user_id);
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

    } elseif ($method === 'POST' && isset($_FILES['file'])) {
        // 上传文件
        $file = $_FILES['file'];

        // 校验文件类型，仅支持图片
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['code' => 400, 'msg' => '仅支持图片文件上传']);
            exit;
        }

        // 校验文件大小，最大5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['code' => 400, 'msg' => '文件大小不能超过5MB']);
            exit;
        }

        // 生成唯一文件名
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $ext;
        $save_path = '../uploads/' . $new_name;

        // 移动上传文件
        if (!move_uploaded_file($file['tmp_name'], $save_path)) {
            echo json_encode(['code' => 500, 'msg' => '文件保存失败']);
            exit;
        }

        // 保存到数据库，使用real_user_id
        $id = $uploadModel->addFile(
            $real_user_id,
            $file['name'],
            'uploads/' . $new_name,
            $file['type'],
            $file['size']
        );

        echo json_encode([
            'code' => 200,
            'msg' => '上传成功',
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

        $file = $uploadModel->getFileById($id);
        if (!$file) {
            echo json_encode(['code' => 404, 'msg' => '文件不存在']);
            exit;
        }

        if (!is_admin() && $file['user_id'] != $real_user_id) {
            echo json_encode(['code' => 403, 'msg' => '无权限删除此文件']);
            exit;
        }

        // 删除物理文件
        if (file_exists('../' . $file['file_path'])) {
            unlink('../' . $file['file_path']);
        }

        $result = $uploadModel->deleteFile($id);

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

        if (!is_admin()) {
            foreach ($ids as $id) {
                $file = $uploadModel->getFileById($id);
                if ($file && $file['user_id'] != $real_user_id) {
                    echo json_encode(['code' => 403, 'msg' => '无权限删除部分文件']);
                    exit;
                }
            }
        }

        // 删除物理文件
        foreach ($ids as $id) {
            $file = $uploadModel->getFileById($id);
            if ($file && file_exists('../' . $file['file_path'])) {
                unlink('../' . $file['file_path']);
            }
        }

        $result = $uploadModel->batchDeleteFile($ids);

        echo json_encode([
            'code' => 200,
            'msg' => '批量删除成功',
            'data' => ['count' => $result]
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['code' => 500, 'msg' => '系统错误: ' . $e->getMessage()]);
}
