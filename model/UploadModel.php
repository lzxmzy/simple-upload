<?php
// model/UploadModel.php
// 文件上传模型，处理文件上传相关的数据库操作

require_once __DIR__ . '/../config/pdo_connect.php';

class UploadModel {
    private $pdo;

    public function __construct() {
        $this->pdo = get_pdo();
    }

    /**
     * 根据ID获取文件信息
     * @param int $id
     * @return array|null
     */
    public function getFileById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, file_name, file_path, file_type, file_size, create_time 
            FROM upload_file WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * 获取指定用户的文件列表（分页）
     * @param int $user_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getFileListByUser($user_id, $page, $limit) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
            SELECT f.*, u.username 
            FROM upload_file f
            LEFT JOIN user u ON f.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 获取指定用户的文件总数
     * @param int $user_id
     * @return int
     */
    public function getFileCountByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM upload_file WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    }

    /**
     * 添加文件记录
     * @param int $user_id
     * @param string $file_name
     * @param string $file_path
     * @param string $file_type
     * @param int $file_size
     * @return int 新文件ID
     */
    public function addFile($user_id, $file_name, $file_path, $file_type, $file_size) {
        $stmt = $this->pdo->prepare("
            INSERT INTO upload_file (user_id, file_name, file_path, file_type, file_size) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $file_name, $file_path, $file_type, $file_size]);
        return $this->pdo->lastInsertId();
    }

    /**
     * 删除文件
     * @param int $id
     * @return bool
     */
    public function deleteFile($id) {
        $stmt = $this->pdo->prepare("DELETE FROM upload_file WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 批量删除文件
     * @param array $ids
     * @return int 删除的行数
     */
    public function batchDeleteFile($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM upload_file WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }
}
