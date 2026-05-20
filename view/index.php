<?php
// view/index.php
// SPA单页面应用，所有登录后的功能都在这里
// 无刷新切换不同的功能模块

require_once '../config/check.php';
require_once '../config/check_role.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    首页 - 学生留言管理系统
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<!-- 导航栏 -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">学生留言管理系统</div>
        <div class="nav-menu">
            <a href="index.php" class="active">留言板</a>
            <a href="upload.php">文件管理</a>
            <?php if (is_super_admin()): ?>
                <a href="user_manage.php">用户管理</a>
                <a href="reset.php">重置密码</a>
            <?php endif; ?>
            <a href="../controller/logout.php">退出</a>
        </div>
    </div>
</nav>

<!-- 管理员视图切换提示 -->
<?php if (is_admin()): ?>
    <div class="switch-notice">
        <span id="switchStatus">当前视图：<?php echo $real_user_name; ?></span>
        <input type="number" id="switchUserId" placeholder="输入用户ID切换视图">
        <button onclick="switchUser()" class="btn btn-sm">切换</button>
        <button onclick="switchBack()" class="btn btn-sm">切回自己</button>
    </div>
<?php endif; ?>

<div class="container">
    <!-- 留言发布区域 -->
    <div class="card">
        <h2>发布留言</h2>
        <form id="msgForm">
            <div class="form-group">
                <textarea name="content" id="msgContent" required rows="4" placeholder="写下你的留言..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">发布</button>
        </form>
    </div>

    <!-- 留言列表 -->
    <div class="card">
        <div class="card-header">
            <h2>留言列表</h2>
            <div class="batch-actions">
                <button onclick="batchDeleteMsg()" class="btn btn-danger btn-sm">批量删除</button>
            </div>
        </div>
        <div id="msgList" class="list-container">
            <!-- 留言列表将通过JS动态加载 -->
        </div>
        <!-- 分页 -->
        <div id="pagination" class="pagination">
            <!-- 分页将通过JS动态加载 -->
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let selectedIds = [];

    // 加载留言列表
    async function loadMsgList(page = 1) {
        currentPage = page;
        try {
            const res = await fetch(`../controller/MsgController.php?page=${page}`);
            const data = await res.json();

            if (data.code !== 200) {
                alert(data.msg);
                return;
            }

            const list = data.data.list;
            const total = data.data.total;
            const limit = data.data.limit;
            const totalPage = Math.ceil(total / limit);

            // 渲染列表
            const listEl = document.getElementById('msgList');
            listEl.innerHTML = list.map(item => `
                    <div class="list-item">
                        <input type="checkbox" class="item-checkbox" value="${item.id}" onchange="toggleSelect(${item.id}, this.checked)">
                        <div class="item-content">
                            <div class="item-header">
                                <span class="username">${item.username}</span>
                                <span class="time">${item.create_time}</span>
                            </div>
                            <div class="item-text">${escapeHtml(item.content)}</div>
                        </div>
                        <button onclick="deleteMsg(${item.id})" class="btn btn-danger btn-sm">删除</button>
                    </div>
                `).join('');

            // 渲染分页
            renderPagination(page, totalPage);

            // 重置选中状态
            selectedIds = [];
        } catch (e) {
            alert('加载失败');
        }
    }

    // 渲染分页
    function renderPagination(current, total) {
        const pagination = document.getElementById('pagination');
        let html = '';

        if (current > 1) {
            html += `<button onclick="loadMsgList(${current - 1})">上一页</button>`;
        }

        for (let i = 1; i <= total; i++) {
            if (i === current) {
                html += `<button class="active">${i}</button>`;
            } else {
                html += `<button onclick="loadMsgList(${i})">${i}</button>`;
            }
        }

        if (current < total) {
            html += `<button onclick="loadMsgList(${current + 1})">下一页</button>`;
        }

        pagination.innerHTML = html;
    }

    // 切换选中
    function toggleSelect(id, checked) {
        if (checked) {
            selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter(i => i !== id);
        }
    }

    // 添加留言
    document.getElementById('msgForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const content = document.getElementById('msgContent').value;

        const formData = new FormData();
        formData.append('content', content);

        try {
            const res = await fetch('../controller/MsgController.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.code === 200) {
                alert(data.msg);
                this.reset();
                loadMsgList(1);
            } else {
                alert(data.msg);
            }
        } catch (e) {
            alert('网络错误');
        }
    });

    // 删除单条留言
    async function deleteMsg(id) {
        if (!confirm('确定要删除这条留言吗？')) return;

        try {
            const res = await fetch(`../controller/MsgController.php?id=${id}`, {
                method: 'DELETE'
            });
            const data = await res.json();

            alert(data.msg);
            loadMsgList(currentPage);
        } catch (e) {
            alert('网络错误');
        }
    }

    // 批量删除留言
    async function batchDeleteMsg() {
        if (selectedIds.length === 0) {
            alert('请先选择要删除的留言');
            return;
        }

        if (!confirm(`确定要删除选中的 ${selectedIds.length} 条留言吗？`)) return;

        const formData = new FormData();
        formData.append('action', 'batch_delete');
        formData.append('ids', JSON.stringify(selectedIds));

        try {
            const res = await fetch('../controller/MsgController.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.msg);
            loadMsgList(1);
        } catch (e) {
            alert('网络错误');
        }
    }

    // 切换用户视图
    async function switchUser() {
        const userId = document.getElementById('switchUserId').value;
        if (!userId) {
            alert('请输入用户ID');
            return;
        }

        const formData = new FormData();
        formData.append('user_id', userId);

        try {
            const res = await fetch('../controller/switch_user.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.msg);
            if (data.code === 200) {
                document.getElementById('switchStatus').textContent = `当前视图：${data.data.username}`;
                loadMsgList(1);
            }
        } catch (e) {
            alert('网络错误');
        }
    }

    // 切回自己
    async function switchBack() {
        const formData = new FormData();
        formData.append('user_id', '');

        try {
            const res = await fetch('../controller/switch_user.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.msg);
            if (data.code === 200) {
                document.getElementById('switchStatus').textContent = `当前视图：<?php echo $real_user_name; ?>`;
                document.getElementById('switchUserId').value = '';
                loadMsgList(1);
            }
        } catch (e) {
            alert('网络错误');
        }
    }

    // HTML转义
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // 页面加载时初始化
    loadMsgList(1);
</script>
</body>
</html>