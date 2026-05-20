<?php
// config/session_config.php
// 全局Session配置与双ID权限隔离初始化
// 所有页面必须首先引入此文件

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 双ID权限隔离初始化
if (!isset($_SESSION['real_user_id'])) {
    // 未登录状态
    $_SESSION['real_user_id'] = null;
    $_SESSION['switch_user_id'] = null;
    $_SESSION['real_user_role'] = null;
    $_SESSION['real_user_name'] = null; // 这里修复了！
} else {
    // 已登录状态，确保switch_user_id存在
    if (!isset($_SESSION['switch_user_id'])) {
        $_SESSION['switch_user_id'] = $_SESSION['real_user_id'];
    }
}

// 全局变量定义，方便全局调用
$real_user_id = $_SESSION['real_user_id'];
$switch_user_id = $_SESSION['switch_user_id'];
$real_user_role = $_SESSION['real_user_role'];
$real_user_name = $_SESSION['real_user_name'];
?>