# 变更日志

### 2025-07-21

- 适配 Typecho 1.3.0 (PHP 7.4+)
  - 修复 `Restful_Action` 类属性类型声明兼容性问题
  - 更新最低 PHP 版本要求至 7.4.0

### 2025-07-22

- PHP 8.1+ 兼容性修复
  - 修复 `explode()`、`trim()`、`str_replace()`、`mb_substr()`、`htmlspecialchars_decode()`、`strip_tags()`、`implode()`、`array_merge()`、`count()` 等函数在 PHP 8.1+ 下接收 `null` 参数报错的问题
  - 新增 `platform.php: 8.1.0` 配置用于 CI 测试
  - 版本号升级至 1.3.1

### 2025-05-04

- 适配typecho1.2.x
- 新增apiToken校验(非必填)
  - 为空就不校验
- 新增是否校验高敏接口（发布文章、新增标签/分类）
  - 开启后需要携带cookie访问接口
- 修复接口：获取标签、发表评论（传参有修改）
- 新增接口：用户列表、发表文章、新增分类/标签
- 不兼容Typecho1.2以前版本

------
