<?php
// config/check_role.php
// 角色鉴权工具函数

require_once 'session_config.php';

/**
 * 检测当前用户是否拥有指定角色权限
 * @param int $min_role 最小需要的角色ID
 * @return bool
 */
function check_role($min_role) {
    global $real_user_role;
    return $real_user_role >= $min_role;
}

/**
 * 检测是否为管理员
 * @return bool
 */
function is_admin() {
    return check_role(2);
}

/**
 * 检测是否为超级管理员
 * @return bool
 */
function is_super_admin() {
    return check_role(3);
}
?>