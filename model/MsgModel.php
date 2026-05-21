<?php
// model/MsgModel.php
// 留言模型，处理留言相关的数据库操作

require_once __DIR__ . '/../config/pdo_connect.php';

class MsgModel {
    private $pdo;

    public function __construct() {
        $this->pdo = get_pdo();
    }

    /**
     * 根据ID获取留言信息
     * @param int $id
     * @return array|null
     */
    public function getMsgById($id) {
        $stmt = $this->pdo->prepare("SELECT id, user_id, content, create_time, update_time FROM message WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * 获取指定用户的留言列表（分页）
     * @param int $user_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getMsgListByUser($user_id, $page, $limit) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.username 
            FROM message m
            LEFT JOIN user u ON m.user_id = u.id
            WHERE m.user_id = ?
            ORDER BY m.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 获取指定用户的留言总数
     * @param int $user_id
     * @return int
     */
    public function getMsgCountByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM message WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    }

    /**
     * 添加留言
     * @param int $user_id
     * @param string $content
     * @return int 新留言ID
     */
    public function addMsg($user_id, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO message (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $content]);
        return $this->pdo->lastInsertId();
    }

    /**
     * 删除留言
     * @param int $id
     * @return bool
     */
    public function deleteMsg($id) {
        $stmt = $this->pdo->prepare("DELETE FROM message WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 批量删除留言
     * @param array $ids
     * @return int 删除的行数
     */
    public function batchDeleteMsg($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM message WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }
}
