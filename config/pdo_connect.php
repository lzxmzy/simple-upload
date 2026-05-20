<?php
// config/pdo_connect.php
// PDO数据库连接，统一连接入口

$pdo = null;

/**
 * 获取PDO数据库连接实例（单例模式）
 * @return PDO
 */
function get_pdo() {
    global $pdo;

    if ($pdo !== null) {
        return $pdo;
    }

    // 数据库配置
    $db_host = 'localhost';
    $db_name = 'student_msg_system';
    $db_user = 'root';
    $db_pass = 'root';

    try {
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die('数据库连接失败: ' . $e->getMessage());
    }
}
?>