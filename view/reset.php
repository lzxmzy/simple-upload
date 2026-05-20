<?php
// view/reset.php
// 重置密码页面

require_once '../config/session_config.php';
require_once '../config/check.php';
require_once '../config/check_role.php';

// 仅超级管理员可访问
if (!is_super_admin()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    重置密码 - 学生留言管理系统
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<!-- 导航栏 -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">学生留言管理系统</div>
        <div class="nav-menu">
            <a href="index.php">首页</a>
            <a href="upload.php">文件管理</a>
            <?php if (is_super_admin()): ?>
                <a href="user_manage.php">用户管理</a>
                <a href="reset.php" class="active">重置密码</a>
            <?php endif; ?>
            <a href="../controller/logout.php">退出</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>重置用户密码</h2>
        <form id="resetForm">
            <div class="form-group">
                <label>用户ID</label>
                <input type="number" name="user_id" required placeholder="请输入要重置密码的用户ID">
            </div>
            <div class="form-group">
                <label>新密码</label>
                <input type="password" name="new_password" required minlength="6" placeholder="新密码，至少6位">
            </div>
            <button type="submit" class="btn btn-primary">重置密码</button>
        </form>
        <div id="msg" class="msg" style="display: none;"></div>
    </div>
</div>

<script>
    document.getElementById('resetForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const res = await fetch('../controller/resetPwd.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            const msgEl = document.getElementById('msg');
            msgEl.textContent = data.msg;
            msgEl.style.display = 'block';
            msgEl.className = data.code === 200 ? 'msg success' : 'msg error';

            if (data.code === 200) {
                this.reset();
            }
        } catch (e) {
            alert('网络错误');
        }
    });
</script>
</body>
</html>