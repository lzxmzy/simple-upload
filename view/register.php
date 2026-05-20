<?php
// view/register.php
// 注册页面

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
    注册 - 学生留言管理系统
    <link rel="stylesheet" href="../css/main.css">
</head>
<body class="auth-page">
<div class="auth-container">
    <h1>学生留言管理系统</h1>
    <h2>用户注册</h2>
    <form id="registerForm" class="auth-form">
        <div class="form-group">
            <label>用户名</label>
            <input type="text" name="username" required minlength="3" maxlength="20" placeholder="3-20位字符">
        </div>
        <div class="form-group">
            <label>邮箱</label>
            <input type="email" name="email" required placeholder="请输入邮箱">
        </div>
        <div class="form-group">
            <label>密码</label>
            <input type="password" name="password" required minlength="6" placeholder="至少6位">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">注册</button>
        </div>
        <div class="auth-links">
            <a href="login.php">已有账号？去登录</a>
        </div>
    </form>
    <div id="errorMsg" class="error-msg" style="display: none;"></div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const res = await fetch('../controller/register.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.code === 200) {
                alert(data.msg);
                window.location.href = 'login.php';
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
</html>