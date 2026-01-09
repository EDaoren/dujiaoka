FROM webdevops/php-nginx:7.4

# 复制应用代码
COPY . /app

# 设置工作目录
WORKDIR /app

# 安装 PHP 依赖
RUN [ "sh", "-c", "composer install --ignore-platform-reqs" ]

# 复制并设置启动脚本
COPY entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/entrypoint.sh

# 设置基础权限
RUN [ "sh", "-c", "chmod -R 755 /app" ]

# 容器启动时执行 entrypoint.sh
CMD ["/app/entrypoint.sh"]
