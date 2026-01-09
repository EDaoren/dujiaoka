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

    # 3. 替换占位符为默认值
    echo "[初始化] 设置默认配置值..."
    sed -i 's/{title}/独角数卡/g' /app/.env
    sed -i 's/{app_key}//g' /app/.env
    sed -i 's/{app_url}/http:\/\/localhost/g' /app/.env
    sed -i 's/{db_host}/mysql/g' /app/.env
    sed -i 's/{db_port}/3306/g' /app/.env
    sed -i 's/{db_database}/dujiaoka/g' /app/.env
    sed -i 's/{db_username}/root/g' /app/.env
    sed -i 's/{db_password}//g' /app/.env
    sed -i 's/{redis_host}/redis/g' /app/.env
    sed -i 's/{redis_password}//g' /app/.env
    sed -i 's/{redis_port}/6379/g' /app/.env
    sed -i 's/{admin_path}/admin/g' /app/.env

    echo "[初始化] 配置文件创建完成"
    echo "[提示] 首次使用请访问 /install 进行安装配置"
else
    echo "[启动] 检测到已有配置文件，跳过初始化"
fi

# 4. 确保必要目录存在并设置权限
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

# 5. 清除缓存（可选，避免旧缓存问题）
echo "[清理] 清除应用缓存..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# 6. 启动队列服务（后台运行）
echo "[服务] 启动队列处理服务..."
php artisan queue:work --sleep=3 --tries=3 >/tmp/queue-work.log 2>&1 &

# 7. 启动 Web 服务
echo "[服务] 启动 Web 服务..."
echo "==================================="
echo "容器启动完成！"
echo "访问地址: http://your-domain:8111"
echo "首次使用: http://your-domain:8111/install"
echo "==================================="

# 使用 supervisord 启动 Nginx 和 PHP-FPM
exec supervisord -n -c /opt/docker/etc/supervisor.conf
