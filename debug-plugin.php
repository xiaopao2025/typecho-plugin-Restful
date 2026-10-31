<?php
/**
 * 调试脚本：在 Typecho 容器内运行
 * 用法：docker cp debug-plugin.php typecho-test:/var/www/html/ && docker exec typecho-test php /var/www/html/debug-plugin.php
 */

require_once __DIR__ . '/index.php';

// 初始化 Typecho
Typecho\Common::init();
$db = Typecho\Db::get();

// 1. 检查插件表状态
echo "=== 1. 检查 plugins 选项 ===\n";
$plugins = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'plugins'));
if ($plugins) {
    echo "plugins 值: " . $plugins['value'] . "\n";
    $decoded = json_decode($plugins['value'], true);
    echo "已激活插件: " . implode(', ', array_keys($decoded['activated'] ?? [])) . "\n";
} else {
    echo "plugins 选项不存在！\n";
}

// 2. 检查 Restful 插件配置
echo "\n=== 2. 检查 Restful 插件配置 ===\n";
$config = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'plugin:Restful'));
if ($config) {
    echo "plugin:Restful 值: " . $config['value'] . "\n";
} else {
    echo "plugin:Restful 配置不存在！\n";
}

// 3. 检查路由表
echo "\n=== 3. 检查路由表 ===\n";
$routing = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'routingTable'));
if ($routing) {
    $routes = json_decode($routing['value'], true);
    $restRoutes = array_filter($routes[0] ?? [], function($k) { return strpos($k, 'rest_') === 0; }, ARRAY_FILTER_USE_KEY);
    echo "Restful 路由数量: " . count($restRoutes) . "\n";
    foreach ($restRoutes as $name => $route) {
        echo "  - $name: {$route['url']} -> {$route['widget']}::{$route['action']}\n";
    }
} else {
    echo "路由表不存在！\n";
}

// 4. 手动激活插件（测试用）
echo "\n=== 4. 测试手动激活 ===\n";
require_once __DIR__ . '/usr/plugins/Restful/Plugin.php';
require_once __DIR__ . '/usr/plugins/Restful/Action.php';

try {
    Restful_Plugin::activate();
    echo "Restful_Plugin::activate() 执行成功\n";
    
    Typecho\Plugin::activate("Restful");
    echo "Typecho\\Plugin::activate('Restful') 执行成功\n";
    
    $plugins = Typecho\Plugin::export();
    $updateResult = $db->query($db->update("table.options")->rows(["value" => json_encode($plugins)])->where("name = ?", "plugins"));
    echo "数据库更新结果: " . var_export($updateResult, true) . "\n";
    
    // 验证
    $verify = $db->fetchRow($db->select("value")->from("table.options")->where("name = ?", "plugins"));
    echo "验证结果: " . ($verify ? "成功" : "失败") . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}

// 5. 测试 API 响应
echo "\n=== 5. 测试 API 直接调用 ===\n";
try {
    $request = Typecho\Request::getInstance();
    $response = Typecho\Response::getInstance();
    
    // 模拟 GET /api/posts
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/api/posts';
    $_SERVER['PATH_INFO'] = '/api/posts';
    $_GET = [];
    
    $action = new Restful_Action($request, $response);
    $action->execute();
    
} catch (Exception $e) {
    echo "API 测试错误: " . $e->getMessage() . "\n";
}
