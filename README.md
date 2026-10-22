# VND_bankpay

越南银行支付系统。后端使用 ThinkPHP 8.1.4，包含后台、商户、码商、开放支付接口和任务模块。

## 环境与安装

- PHP 8.2+，CLI、Web 站点和定时任务使用同一版本。
- PHP 扩展：ctype、curl、fileinfo、gd（含 FreeType）、iconv、mbstring、pdo_mysql、redis。
- MySQL 5.7+，Redis；站点根目录为 `api/public`。

```bash
cd api
composer install
composer validate --strict --no-check-publish
composer check-platform-reqs
php think version
```

生产安装使用 `composer install --no-dev --optimize-autoloader`。保留现有数据库连接、JWT 密钥、支付渠道和回调配置，不要把真实密钥提交到仓库。

## ThinkPHP 8 升级说明

- 框架升级到 8.1.4，ORM 使用 TP8 支持的 3.x，保留现有模型和资金事务逻辑。
- 多应用 1.1.1、文件系统 3.x、官方验证码 3.x、JWT 7.x、模板驱动 2.x / 模板引擎 3.x。
- 保留支付收银模板、VietQR HTTP 客户端与二维码依赖。`extend/Vietqr.php` 通过 classmap 加载，不再使用空命名空间 PSR-0。
- 验证码仍返回 `key` 与 base64 图片，校验使用 Redis，不依赖 Session cookie。旧验证码升级后需要刷新。
- HS256 JWT 密钥至少 32 字节。足够长的原有密钥和 token 可以沿用；更换短密钥后需要重新登录。新密钥使用 `random_bytes`。
- 修复查询字段读取、关联列表排序歧义、Flysystem 3 删除返回值、PHP 可空参数和二维码空 logo 参数的兼容问题。
- 移除未调用的 PhpSpreadsheet 及其专用间接依赖、废弃 FormField 服务/验证器、注释示例控制器及无效配置。真实支付模板、任务和业务工具保留。
- 升级框架不需要改动现有表结构；已有业务 SQL 迁移应按原项目部署流程管理。

切换 PHP 后应检查定时任务中 `OrderExpired`、`OrderSettlement` 使用的 PHP 路径。不要为验证升级而在真实数据上反复执行结算或支付通知命令。

清理旧的配置、路由或模板缓存前先确认路径；不要整目录删除 `runtime`，其中可能有 `jwt_key` 等持久化文件。PHP 8 不支持 `track_errors`，也不能加载 PHP 7 的扩展 DLL。

CI 在 PHP 8.2 / 8.3 / 8.4 / 8.5 下检查依赖、语法及框架启动；支付交易、外部 VietQR 服务及资金结算仍需在隔离环境回归。

## 控制器入口清理

后台、商户和码商控制器直接继承 `CrudController` 提供的公开 CRUD 方法，无需额外引入 trait 或声明方法可见性。各控制器可覆盖通用方法实现业务逻辑。动态统计、组件二维码和上传调用均保留。

支付开放 API、收银页、回调、设备和任务方法作为独立业务入口保留。`check_action_exists` 仅允许绑定公开方法。已移除的操作权限同步从初始化 SQL 中清理；旧库可备份后执行 `doc/20261022_controller_cleanup.sql`，再清理权限缓存。
