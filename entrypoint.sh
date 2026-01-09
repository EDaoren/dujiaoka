#!/bin/bash
set -e

echo "==================================="
echo "独角数卡 Docker 容器启动中..."
echo "==================================="

# 1. 检查并创建 .env 文件
if [ ! -f /app/.env ]; then
    echo "[初始化] 首次启动，创建 .env 配置文件..."
    cp /app/.env.example /app/.env

    # 2. 生成 APP_KEY
    echo "[初始化] 生成应用密钥..."
    php artisan key:generate --force

    # 3. 从环境变量读取配置（支持自定义）
    echo "[初始化] 应用环境变量配置..."

    # 基础配置
    APP_ENV_VALUE=${APP_ENV:-"local"}
    APP_DEBUG_VALUE=${APP_DEBUG:-"false"}
    APP_URL_VALUE=${APP_URL:-"http://localhost"}
    APP_HTTPS_VALUE=${APP_HTTPS:-"false"}

    # 后台配置
    ADMIN_ROUTE_PREFIX_VALUE=${ADMIN_ROUTE_PREFIX:-"admin"}
    ADMIN_LANGUAGE_VALUE=${ADMIN_LANGUAGE:-"zh_CN"}

    # 数据库配置（可选，如果不设置则通过安装向导配置）
    DB_HOST_VALUE=${DB_HOST:-"mysql"}
    DB_PORT_VALUE=${DB_PORT:-"3306"}
    DB_DATABASE_VALUE=${DB_DATABASE:-"dujiaoka"}
    DB_USERNAME_VALUE=${DB_USERNAME:-"root"}
    DB_PASSWORD_VALUE=${DB_PASSWORD:-""}

    # Redis配置（可选）
    REDIS_HOST_VALUE=${REDIS_HOST:-"redis"}
    REDIS_PORT_VALUE=${REDIS_PORT:-"6379"}
    REDIS_PASSWORD_VALUE=${REDIS_PASSWORD:-""}

    # 4. 替换 .env 文件中的占位符
    echo "[初始化] 写入配置到 .env 文件..."

    sed -i 's/{title}/独角数卡/g' /app/.env
    sed -i 's/{app_key}//g' /app/.env
    sed -i "s#{app_url}#${APP_URL_VALUE}#g" /app/.env
    sed -i "s/{db_host}/${DB_HOST_VALUE}/g" /app/.env
    sed -i "s/{db_port}/${DB_PORT_VALUE}/g" /app/.env
    sed -i "s/{db_database}/${DB_DATABASE_VALUE}/g" /app/.env
    sed -i "s/{db_username}/${DB_USERNAME_VALUE}/g" /app/.env
    sed -i "s/{db_password}/${DB_PASSWORD_VALUE}/g" /app/.env
    sed -i "s/{redis_host}/${REDIS_HOST_VALUE}/g" /app/.env
    sed -i "s/{redis_password}/${REDIS_PASSWORD_VALUE}/g" /app/.env
    sed -i "s/{redis_port}/${REDIS_PORT_VALUE}/g" /app/.env
    sed -i "s/{admin_path}/${ADMIN_ROUTE_PREFIX_VALUE}/g" /app/.env

    # 设置环境和调试模式
    sed -i "s/APP_ENV=.*/APP_ENV=${APP_ENV_VALUE}/g" /app/.env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=${APP_DEBUG_VALUE}/g" /app/.env

    # 设置 HTTPS 配置
    sed -i "s/ADMIN_HTTPS=.*/ADMIN_HTTPS=${APP_HTTPS_VALUE}/g" /app/.env

    # 设置后台语言
    sed -i "s/DUJIAO_ADMIN_LANGUAGE=.*/DUJIAO_ADMIN_LANGUAGE=${ADMIN_LANGUAGE_VALUE}/g" /app/.env

    # 5. 首次启动时，禁用 Redis，避免连接失败
    # 用户通过安装向导配置后，会自动启用 Redis
    echo "[初始化] 配置缓存和队列驱动..."
    sed -i 's/CACHE_DRIVER=redis/CACHE_DRIVER=file/g' /app/.env
    sed -i 's/QUEUE_CONNECTION=redis/QUEUE_CONNECTION=sync/g' /app/.env

    # 6. 设置 .env 文件权限，确保安装程序可以写入
    chmod 666 /app/.env
    chown application:application /app/.env || true

    echo "[配置] ===== 当前配置 ====="
    echo "[配置] APP_ENV: ${APP_ENV_VALUE}"
    echo "[配置] APP_DEBUG: ${APP_DEBUG_VALUE}"
    echo "[配置] APP_URL: ${APP_URL_VALUE}"
    echo "[配置] ADMIN_HTTPS: ${APP_HTTPS_VALUE}"
    echo "[配置] ADMIN_ROUTE_PREFIX: ${ADMIN_ROUTE_PREFIX_VALUE}"
    echo "[配置] ADMIN_LANGUAGE: ${ADMIN_LANGUAGE_VALUE}"
    echo "[配置] ========================"
    echo "[初始化] 配置文件创建完成"
    echo "[提示] 首次使用请访问应用首页，会自动跳转到安装页面"
else
    echo "[启动] 检测到已有配置文件，跳过初始化"
fi

# 确保 .env 文件始终有正确的权限（无论是否首次启动）
if [ -f /app/.env ]; then
    chmod 666 /app/.env
    chown application:application /app/.env || true
fi

# 7. 确保必要目录存在并设置权限
echo "[检查] 创建必要目录并设置权限..."

# 创建 storage 子目录
mkdir -p /app/storage/logs
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/app/public
mkdir -p /app/bootstrap/cache
mkdir -p /app/public/uploads

# 设置目录权限 - 使用 777 确保 Web 服务器可以写入
# 注意：这里必须在 volume 挂载后执行，所以放在 entrypoint 中
chmod -R 777 /app/storage
chmod -R 777 /app/bootstrap/cache
chmod -R 777 /app/public/uploads

# 特别处理 logs 目录（可能被 volume 挂载）
chmod -R 777 /app/storage/logs
chown -R application:application /app/storage/logs || true

echo "[权限] 目录权限设置完成"

# 8. 清除缓存（可选，避免旧缓存问题）
echo "[清理] 清除应用缓存..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 9. 检查是否需要启动队列服务
# 如果配置了 Redis 队列，才启动队列服务
QUEUE_DRIVER=$(grep "^QUEUE_CONNECTION=" /app/.env | cut -d'=' -f2)
if [ "$QUEUE_DRIVER" = "redis" ]; then
    echo "[服务] 启动队列处理服务（Redis 驱动）..."
    php artisan queue:work --sleep=3 --tries=3 >/tmp/queue-work.log 2>&1 &
else
    echo "[服务] 队列使用同步模式，不启动后台服务"
fi

# 10. 启动 Web 服务
echo "[服务] 启动 Web 服务..."
echo "==================================="
echo "容器启动完成！"
echo "访问地址: ${APP_URL:-http://localhost:8111}"
echo "后台地址: ${APP_URL:-http://localhost:8111}/${ADMIN_ROUTE_PREFIX:-admin}"
echo "首次使用: 访问首页会自动跳转到安装页面"
echo "==================================="

# 使用 supervisord 启动 Nginx 和 PHP-FPM
exec supervisord -n -c /opt/docker/etc/supervisor.conf
