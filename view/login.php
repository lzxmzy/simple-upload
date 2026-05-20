<?php
/**
 * 【文件顶级注释】
 * 功能：用户登录视图
 * 模块归属：视图层
 * 对应规范：文档第1条半前后端分离、第109条海洋流动背景规范、第142条强制引入顺序
 * 样式规则：仅引入唯一main.css，通过page-login类名隔离专属样式
 * 引入规则：游客页面，仅引入session_config.php
 */

// 严格按规范引入：游客页面仅引入session_config
require_once "../config/session_config.php";
// 已登录自动跳转首页
if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户登录</title>
    <!-- 全项目唯一引入的CSS文件，所有样式全部收纳 -->
    <link rel="stylesheet" href="../css/main.css">
</head>
<!-- 专属类名，main.css通过此类名隔离登录页专属样式 -->
<?php
// view/login.php
// 登录页面

require_once '../config/session_config.php';

// 如果已登录，跳转到主页
if ($real_user_id !== null) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    登录 - 学生留言管理系统
    <link rel="stylesheet" href="../css/main.css">
</head>
<body class="auth-page">
<div class="auth-container">
    <h1>学生留言管理系统</h1>
    <h2>用户登录</h2>
    <form id="loginForm" class="auth-form">
        <div class="form-group">
            <label>用户名</label>
            <input type="text" name="username" required placeholder="请输入用户名">
        </div>
        <div class="form-group">
            <label>密码</label>
            <input type="password" name="password" required placeholder="请输入密码">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">登录</button>
        </div>
        <div class="auth-links">
            <a href="register.php">没有账号？去注册</a>
        </div>
    </form>
    <div id="errorMsg" class="error-msg" style="display: none;"></div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const res = await fetch('../controller/login.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.code === 200) {
                alert(data.msg);
                window.location.href = 'index.php';
            } else {
                document.getElementById('errorMsg').textContent = data.msg;
                document.getElementById('errorMsg').style.display = 'block';
            }
        } catch (e) {
            document.getElementById('errorMsg').textContent = '网络错误';
            document.getElementById('errorMsg').style.display = 'block';
        }
    });
</script>
</body>
</html>dy class="page-login">
<div class="form-box">
    <h2>用户登录</h2>
    <form action="../controller/login.php" method="POST">
        <div class="input-item">
            <input type="text" placeholder="请输入用户名" name="username" required>
        </div>
        <div class="input-item">
            <input type="password" placeholder="请输入密码" name="password" required>
        </div>
        <button type="submit" class="submit-btn">立即登录</button>
    </form>
    <button class="jump-btn" onclick="window.location.href='register.php'">没有账号？前往注册</button>
    <button class="jump-btn" onclick="window.location.href='reset.php'">忘记密码？重置密码</button>
</div>
</body>
</html>