<?php
// view/upload.php
// 文件上传管理页面

require_once '../config/check.php';
require_once '../config/check_role.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    文件管理 - 学生留言管理系统
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<!-- 导航栏 -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">学生留言管理系统</div>
        <div class="nav-menu">
            <a href="index.php">留言板</a>
            <a href="upload.php" class="active">文件管理</a>
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
    <!-- 文件上传区域 -->
    <div class="card">
        <h2>上传图片</h2>
        <form id="uploadForm">
            <div class="form-group">
                <input type="file" name="file" id="fileInput" accept="image/*" required>
                <p class="tip">仅支持图片文件，最大5MB</p>
            </div>
            <button type="submit" class="btn btn-primary">上传</button>
        </form>
    </div>

    <!-- 文件列表 -->
    <div class="card">
        <div class="card-header">
            <h2>文件列表</h2>
            <div class="batch-actions">
                <button onclick="batchDeleteFile()" class="btn btn-danger btn-sm">批量删除</button>
            </div>
        </div>
        <div id="fileList" class="file-list">
            <!-- 文件列表将通过JS动态加载 -->
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

    // 加载文件列表
    async function loadFileList(page = 1) {
        currentPage = page;
        try {
            const res = await fetch(`../controller/UploadController.php?page=${page}`);
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
            const listEl = document.getElementById('fileList');
            listEl.innerHTML = list.map(item => `
                    <div class="list-item">
                        <input type="checkbox" class="item-checkbox" value="${item.id}" onchange="toggleSelect(${item.id}, this.checked)">
                        <div class="item-content">
                            <div class="item-header">
                                <span class="username">${item.username}</span>
                                <span class="time">${item.create_time}</span>
                            </div>
                            <div class="file-info">
                                <img src="../${item.file_path}" class="file-preview" alt="${item.file_name}">
                                <div>
                                    <div class="file-name">${item.file_name}</div>
                                    <div class="file-size">${formatFileSize(item.file_size)}</div>
                                </div>
                            </div>
                        </div>
                        <button onclick="deleteFile(${item.id})" class="btn btn-danger btn-sm">删除</button>
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
            html += `<button onclick="loadFileList(${current - 1})">上一页</button>`;
        }

        for (let i = 1; i <= total; i++) {
            if (i === current) {
                html += `<button class="active">${i}</button>`;
            } else {
                html += `<button onclick="loadFileList(${i})">${i}</button>`;
            }
        }

        if (current < total) {
            html += `<button onclick="loadFileList(${current + 1})">下一页</button>`;
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

    // 上传文件
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const res = await fetch('../controller/UploadController.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.code === 200) {
                alert(data.msg);
                this.reset();
                loadFileList(1);
            } else {
                alert(data.msg);
            }
        } catch (e) {
            alert('网络错误');
        }
    });

    // 删除单条文件
    async function deleteFile(id) {
        if (!confirm('确定要删除这个文件吗？')) return;

        try {
            const res = await fetch(`../controller/UploadController.php?id=${id}`, {
                method: 'DELETE'
            });
            const data = await res.json();

            alert(data.msg);
            loadFileList(currentPage);
        } catch (e) {
            alert('网络错误');
        }
    }

    // 批量删除文件
    async function batchDeleteFile() {
        if (selectedIds.length === 0) {
            alert('请先选择要删除的文件');
            return;
        }

        if (!confirm(`确定要删除选中的 ${selectedIds.length} 个文件吗？`)) return;

        const formData = new FormData();
        formData.append('action', 'batch_delete');
        formData.append('ids', JSON.stringify(selectedIds));

        try {
            const res = await fetch('../controller/UploadController.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.msg);
            loadFileList(1);
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
                loadFileList(1);
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
                loadFileList(1);
            }
        } catch (e) {
            alert('网络错误');
        }
    }

    // 格式化文件大小
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }

    // 页面加载时初始化
    loadFileList(1);
</script>
</body>
</html>