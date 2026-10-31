# 本地 Docker 调试指南

## 1. 启动环境

```bash
cd /mnt/c/project/typecho-plugin-Restful
docker-compose up -d
```

等待 10-15 秒让服务完全启动。

## 2. 访问网站

- 前台：http://localhost:8080
- 后台：http://localhost:8080/admin
- 用户名：admin
- 密码：admin123

## 3. 验证插件

### 方式 A：后台查看
1. 登录后台
2. 进入 "控制台" → "插件"
3. 应看到 "Restful" 插件，点击 "启用"

### 方式 B：命令行调试（推荐）

```bash
# 复制调试脚本到容器
docker cp debug-plugin.php typecho-test:/var/www/html/

# 在容器内运行
docker exec typecho-test php /var/www/html/debug-plugin.php
```

输出会显示：
1. `plugins` 选项值（是否包含 Restful）
2. `plugin:Restful` 配置
3. 路由表中的 Restful 路由
4. 手动激活测试结果
5. API 直接调用测试

## 4. 测试 API 端点

```bash
# 文章列表
curl http://localhost:8080/api/posts

# 页面列表
curl http://localhost:8080/api/pages

# 分类列表
curl http://localhost:8080/api/categories

# 标签列表
curl http://localhost:8080/api/tags

# 设置项
curl http://localhost:8080/api/settings
```

## 5. 常见问题排查

### 插件不显示/无法启用
```bash
# 检查文件权限
docker exec typecho-test ls -la /var/www/html/usr/plugins/Restful/

# 检查 PHP 错误日志
docker exec typecho-test tail -f /var/log/apache2/error.log
```

### API 返回 404/500
```bash
# 检查路由注册
docker exec typecho-test php /var/www/html/debug-plugin.php 2>&1 | grep -A 20 "路由表"

# 检查 Apache rewrite
docker exec typecho-test cat /var/www/html/.htaccess
```

### 数据库连接问题
```bash
# 测试数据库连接
docker exec typecho-mysql mysql -u typecho -ptypecho -e "SHOW DATABASES;"
```

## 6. 清理环境

```bash
docker-compose down -v  # -v 删除数据卷（完全重置）
```

## 7. 调试 CI 失败

如果 CI 失败但本地通过，对比差异：
- PHP 版本：`docker exec typecho-test php -v`
- Typecho 版本：查看 `joyqi/typecho:nightly-php8.2-apache` 对应的 commit
- 扩展差异：`docker exec typecho-test php -m`
