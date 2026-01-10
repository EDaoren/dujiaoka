#!/bin/bash
set -e

echo "==================================="
echo "独角数卡 Docker 容器启动中..."
echo "==================================="

# ============================================
# 第一步：更新 .env.example 模板（用于安装向导）
# ============================================
# 安装向导会读取 .env.example 并写入到 .env
# 所以我们需要先根据环境变量更新 .env.example
if [ -f /app/.env.example ]; then
    echo "[1/6] 根据环境变量更新 .env.example 模板..."

    # 只更新用户可能通过环境变量配置的关键项
    [ ! -z "${APP_ENV}" ] && sed -i "s/^APP_ENV=.*/APP_ENV=${APP_ENV}/g" /app/.env.example
    [ ! -z "${APP_DEBUG}" ] && sed -i "s/^APP_DEBUG=.*/APP_DEBUG=${APP_DEBUG}/g" /app/.env.example
    [ ! -z "${APP_HTTPS}" ] && sed -i "s/^ADMIN_HTTPS=.*/ADMIN_HTTPS=${APP_HTTPS}/g" /app/.env.example
    [ ! -z "${ADMIN_LANGUAGE}" ] && sed -i "s/^DUJIAO_ADMIN_LANGUAGE=.*/DUJIAO_ADMIN_LANGUAGE=${ADMIN_LANGUAGE}/g" /app/.env.example
    [ ! -z "${ADMIN_ROUTE_PREFIX}" ] && sed -i "s/^ADMIN_ROUTE_PREFIX=.*/ADMIN_ROUTE_PREFIX=${ADMIN_ROUTE_PREFIX}/g" /app/.env.example

    echo "[1/6] .env.example 更新完成"
fi

# ============================================
# 第二步：智能检测数据库是否已安装
# ============================================
echo "[2/6] 检测数据库安装状态..."

if [ ! -f /app/install.lock ]; then
    echo "[2/6] install.lock 不存在，检查数据库是否已初始化..."

    # 使用 PHP 检测数据库中是否存在核心表
    php -r "
    try {
        require '/app/vendor/autoload.php';
        \$app = require_once '/app/bootstrap/app.php';
        \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        // 尝试查询核心表 admin_users
        \Illuminate\Support\Facades\DB::select('SELECT 1 FROM admin_users LIMIT 1');

        // 查询成功 = 数据库已安装
        file_put_contents('/app/install.lock', 'Auto created by entrypoint at ' . date('Y-m-d H:i:s'));
        echo '[2/6] 数据库已安装，已自动创建 install.lock' . PHP_EOL;
        exit(0);
    } catch (Exception \$e) {
        // 查询失败 = 数据库未安装或配置错误
        echo '[2/6] 数据库未初始化，保持安装向导可用' . PHP_EOL;
        exit(0);
    }
    " 2>/dev/null || echo "[2/6] 数据库检测失败，跳过（可能是首次安装）"
else
    echo "[2/6] install.lock 已存在，跳过检测"
fi

# ============================================
# 第三步：处理 .env 文件
# ============================================
echo "[3/6] 处理 .env 配置文件..."

# .env 文件已经在镜像中，直接更新即可
# 检查是否需要生成 APP_KEY
if grep -q "^APP_KEY=$" /app/.env || grep -q "^APP_KEY=\s*$" /app/.env; then
    echo "[3/6] 生成应用密钥..."
    php artisan key:generate --force
fi

# 根据环境变量更新关键配置
# 这样用户修改 docker-compose.yml 后重启容器就能生效
[ ! -z "${APP_URL}" ] && sed -i "s#^APP_URL=.*#APP_URL=${APP_URL}#g" /app/.env
[ ! -z "${APP_ENV}" ] && sed -i "s/^APP_ENV=.*/APP_ENV=${APP_ENV}/g" /app/.env
[ ! -z "${APP_DEBUG}" ] && sed -i "s/^APP_DEBUG=.*/APP_DEBUG=${APP_DEBUG}/g" /app/.env
[ ! -z "${APP_HTTPS}" ] && sed -i "s/^ADMIN_HTTPS=.*/ADMIN_HTTPS=${APP_HTTPS}/g" /app/.env
[ ! -z "${ADMIN_ROUTE_PREFIX}" ] && sed -i "s/^ADMIN_ROUTE_PREFIX=.*/ADMIN_ROUTE_PREFIX=${ADMIN_ROUTE_PREFIX}/g" /app/.env
[ ! -z "${ADMIN_LANGUAGE}" ] && sed -i "s/^DUJIAO_ADMIN_LANGUAGE=.*/DUJIAO_ADMIN_LANGUAGE=${ADMIN_LANGUAGE}/g" /app/.env

# 确保权限正确
chmod 666 /app/.env
chown application:application /app/.env || true

echo "[3/6] .env 配置完成"

# ============================================
# 第四步：创建必要目录并设置权限
# ============================================
echo "[4/6] 设置目录权限..."

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

echo "[4/6] 权限设置完成"

# ============================================
# 第五步：清除缓存
# ============================================
echo "[5/6] 清除应用缓存..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
echo "[5/6] 缓存清除完成"

# ============================================
# 第六步：启动服务
# ============================================
echo "[6/6] 启动服务..."

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
echo ""
if [ ! -f /app/install.lock ]; then
    echo "【首次使用】"
    echo "请访问: ${APP_URL:-http://localhost:8111}/install"
    echo "通过安装向导完成数据库配置"
fi
echo "==================================="

# 启动 supervisord（Nginx + PHP-FPM）
exec supervisord -n -c /opt/docker/etc/supervisor.conf
