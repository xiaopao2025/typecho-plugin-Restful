#!/bin/bash
# 本地 Docker 测试脚本

set -e

echo "=== 启动 Typecho 容器 ==="
docker-compose up -d

echo "=== 等待服务就绪 ==="
sleep 10

# 检查 Typecho 是否安装完成
echo "=== 检查 Typecho 安装状态 ==="
for i in {1..30}; do
    if curl -s http://localhost:8080/ | grep -q "Hello World"; then
        echo "Typecho 已安装"
        break
    fi
    echo "等待安装完成... ($i/30)"
    sleep 2
done

# 检查插件是否显示在后台
echo "=== 检查插件是否加载 ==="
curl -s http://localhost:8080/admin/ | grep -i "restful" || echo "需要登录后台查看"

# 测试 API 端点
echo "=== 测试 API 端点 ==="
echo "GET /api/posts:"
curl -s http://localhost:8080/api/posts | head -c 200
echo ""

echo "GET /api/pages:"
curl -s http://localhost:8080/api/pages | head -c 200
echo ""

echo "GET /api/categories:"
curl -s http://localhost:8080/api/categories | head -c 200
echo ""

echo "GET /api/tags:"
curl -s http://localhost:8080/api/tags | head -c 200
echo ""

echo "GET /api/settings:"
curl -s http://localhost:8080/api/settings | head -c 200
echo ""

echo "=== 测试完成 ==="
echo "访问 http://localhost:8080 查看网站"
echo "访问 http://localhost:8080/admin 登录后台 (admin/admin123)"
