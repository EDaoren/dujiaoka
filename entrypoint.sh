#!/bin/bash
set -e

echo "==================================="
echo "独角数卡 Docker 容器启动中..."
echo "==================================="

# ============================================
# 第一步：从环境变量生成 .env 文件
# ============================================
echo "[1/4] 从环境变量生成 .env 配置文件..."

# 从模板复制
cp /app/.env.example /app/.env

# 直接使用环境变量中的 APP_KEY（必须包含 base64: 前缀）
echo "[1/4] 使用环境变量中的 APP_KEY"

# 替换所有占位符（使用 | 作为分隔符避免特殊字符冲突）
sed -i "s|{title}|${APP_NAME:-独角数卡}|g" /app/.env
sed -i "s|{app_url}|${APP_URL:-http://localhost:8111}|g" /app/.env
sed -i "s|{app_key}|${APP_KEY}|g" /app/.env
sed -i "s|{db_host}|${DB_HOST}|g" /app/.env
sed -i "s|{db_port}|${DB_PORT:-3306}|g" /app/.env
sed -i "s|{db_database}|${DB_DATABASE}|g" /app/.env
sed -i "s|{db_username}|${DB_USERNAME}|g" /app/.env
sed -i "s|{db_password}|${DB_PASSWORD}|g" /app/.env
sed -i "s|{redis_host}|${REDIS_HOST}|g" /app/.env
sed -i "s|{redis_password}|${REDIS_PASSWORD:-null}|g" /app/.env
sed -i "s|{redis_port}|${REDIS_PORT:-6379}|g" /app/.env
sed -i "s|{admin_path}|${ADMIN_ROUTE_PREFIX:-admin}|g" /app/.env

# 更新其他配置项（同样使用 | 分隔符）
sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV:-production}|g" /app/.env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG:-false}|g" /app/.env
sed -i "s|^ADMIN_HTTPS=.*|ADMIN_HTTPS=${APP_HTTPS:-false}|g" /app/.env
sed -i "s|^DUJIAO_ADMIN_LANGUAGE=.*|DUJIAO_ADMIN_LANGUAGE=${ADMIN_LANGUAGE:-zh_CN}|g" /app/.env

# 设置权限
chmod 666 /app/.env
chown application:application /app/.env || true

echo "[1/4] .env 配置文件生成完成"

# ============================================
# 第二步：检测数据库并自动初始化
# ============================================
echo "[2/4] 检测数据库并自动初始化..."

php -r "
try {
    require '/app/vendor/autoload.php';
    \$app = require_once '/app/bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo '[2/4] 尝试连接数据库...' . PHP_EOL;

    // 检查 admin_users 表是否存在
    \Illuminate\Support\Facades\DB::select('SELECT 1 FROM admin_users LIMIT 1');

    echo '[2/4] 数据库已存在，跳过 SQL 导入' . PHP_EOL;
} catch (Exception \$e) {
    // 表不存在，需要导入 SQL
    echo '[2/4] 数据库未初始化，开始导入 install.sql...' . PHP_EOL;

    try {
        \$sql = file_get_contents('/app/database/sql/install.sql');
        \Illuminate\Support\Facades\DB::unprepared(\$sql);
        echo '[2/4] 数据库初始化完成' . PHP_EOL;
    } catch (Exception \$sqlError) {
        echo '[错误] 数据库初始化失败: ' . \$sqlError->getMessage() . PHP_EOL;
        exit(1);
    }
}

// 创建 install.lock
file_put_contents('/app/install.lock', 'Auto installed at ' . date('Y-m-d H:i:s'));
echo '[2/4] install.lock 创建完成' . PHP_EOL;
"

# 检查 PHP 脚本执行结果
if [ $? -ne 0 ]; then
    echo "[错误] 数据库初始化失败，请检查配置"
    echo "[提示] 请确保在 docker-compose.yml 中正确配置了 DB_HOST, DB_DATABASE 等环境变量"
    exit 1
fi

# 确保 install.lock 存在（双重保险）
if [ ! -f /app/install.lock ]; then
    echo "Auto created by entrypoint" > /app/install.lock
    chmod 644 /app/install.lock
    chown application:application /app/install.lock || true
fi

echo "[2/4] 数据库检测和初始化完成"

# ============================================
# 第三步：创建必要目录并设置权限
# ============================================
echo "[3/4] 设置目录权限..."

# 创建 storage 子目录
mkdir -p /app/storage/logs \
         /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/app/public \
         /app/bootstrap/cache \
         /app/public/uploads

# 设置权限（使用 777 确保 Web 服务器可以写入）
chmod -R 777 /app/storage /app/bootstrap/cache /app/public/uploads
chown -R application:application /app/storage/logs || true

echo "[3/4] 权限设置完成"

# ============================================
# 第四步：清除缓存并启动服务
# ============================================
echo "[4/4] 清除应用缓存并启动服务..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 检查是否需要启动队列服务
QUEUE_DRIVER=$(grep "^QUEUE_CONNECTION=" /app/.env | cut -d'=' -f2)
if [ "$QUEUE_DRIVER" = "redis" ]; then
    echo "[队列] 启动 Redis 队列处理服务..."
    php artisan queue:work --sleep=3 --tries=3 >/tmp/queue-work.log 2>&1 &
else
    echo "[队列] 使用同步模式，不启动后台队列服务"
fi

echo "==================================="
echo "容器启动完成！"
echo "==================================="
echo "访问地址: ${APP_URL:-http://localhost:8111}"
echo "后台地址: ${APP_URL:-http://localhost:8111}/${ADMIN_ROUTE_PREFIX:-admin}"
echo "默认账号: admin / admin"
echo ""
echo "【注意】首次启动已自动完成数据库初始化"
echo "【提示】容器重启会自动恢复配置，无需重新安装"
echo "==================================="

# 启动 supervisord（Nginx + PHP-FPM）
exec supervisord -n -c /opt/docker/etc/supervisor.conf
