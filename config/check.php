<?php
// config/check.php
// 登录状态检测
// 需要登录才能访问的页面必须引入此文件

require_once 'session_config.php';

if ($real_user_id === null) {
    // 未登录，跳转到登录页面
    header('Location: ../view/login.php');
    exit;
}
