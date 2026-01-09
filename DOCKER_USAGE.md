# 独角数卡 Docker 部署指南

## 快速开始

### 前置要求

1. **安装 Docker 和 Docker Compose**
   - Docker 版本 >= 20.10
   - Docker Compose 版本 >= 2.0

2. **准备数据库和 Redis**
   - MySQL 5.6+ 或 MariaDB（需要创建空数据库）
   - Redis 服务

### 部署步骤

#### 方式一：使用 docker-compose（推荐）

```bash
# 1. 下载或克隆项目
git clone <repository-url>
cd dujiaoka

# 2. 构建并启动容器
docker compose up -d

# 3. 查看容器状态
docker compose ps

# 4. 查看启动日志
docker compose logs -f web
```

#### 方式二：使用预构建镜像

```bash
# 1. 创建 docker-compose.yml 文件
cat > docker-compose.yml <<EOF
services:
  web:
    image: your-registry/dujiaoka:latest
    container_name: dujiaoka
    ports:
      - "8111:80"
    volumes:
      - ./data/install.lock:/app/install.lock
      - ./data/uploads:/app/public/uploads
      - ./data/logs:/app/storage/logs
    environment:
      WEB_DOCUMENT_ROOT: "/app/public"
      TZ: Asia/Shanghai
    restart: always
EOF

# 2. 启动容器
docker compose up -d
```

### 首次安装配置

1. **访问安装向导**
   ```
   http://你的服务器IP:8111/install
   ```

   容器启动后会自动跳转到安装页面

2. **填写数据库信息**
   - 数据库地址：`你的MySQL服务器地址`
   - 数据库端口：`3306`
   - 数据库名：`dujiaoka`（需提前创建）
   - 数据库用户：`root` 或其他用户
   - 数据库密码：`你的密码`

3. **填写 Redis 信息**
   - Redis 地址：`你的Redis服务器地址`
   - Redis 端口：`6379`
   - Redis 密码：`你的密码`（如果有）

4. **完成安装**
   - 安装完成后，访问 `http://你的服务器IP:8111/admin` 登录后台
   - 默认管理员账号密码在安装时设置

## 配置说明

### 端口映射

| 容器内端口 | 主机端口 | 说明 |
|----------|---------|------|
| 80 | 8111 | Web 服务（可修改） |
| 9000 | 9000 | PHP-FPM（可选） |

修改端口：编辑 `docker-compose.yml` 中的 `ports` 配置

### 数据持久化

所有持久化数据保存在 `./data` 目录：

```
data/
├── install.lock      # 安装锁文件
├── uploads/          # 用户上传文件
└── logs/             # 应用日志
```

**重要提示**：
- `.env` 配置文件保存在**容器内部**（`/app/.env`）
- 容器重启不会丢失配置
- 如需备份配置，执行：`docker cp dujiaoka:/app/.env ./env.backup`

### 环境变量

| 变量名 | 默认值 | 说明 |
|-------|--------|------|
| WEB_DOCUMENT_ROOT | /app/public | Web 根目录 |
| TZ | Asia/Shanghai | 时区设置 |

## 常用命令

### 容器管理

```bash
# 启动容器
docker compose up -d

# 停止容器
docker compose stop

# 重启容器
docker compose restart

# 查看容器日志
docker compose logs -f web

# 进入容器
docker compose exec web bash

# 删除容器（数据不会丢失）
docker compose down
```

### 应用管理

```bash
# 清除应用缓存
docker compose exec web php artisan cache:clear

# 查看队列状态
docker compose exec web php artisan queue:work --once

# 查看应用日志
docker compose exec web tail -f storage/logs/laravel.log

# 备份 .env 配置
docker cp dujiaoka:/app/.env ./env.backup

# 恢复 .env 配置
docker cp ./env.backup dujiaoka:/app/.env
docker compose restart
```

## 常见问题

### 1. 容器启动后无法访问

**问题**：访问 `http://localhost:8111` 显示无法访问

**解决**：
```bash
# 检查容器状态
docker compose ps

# 查看容器日志
docker compose logs web

# 检查端口是否被占用
netstat -an | grep 8111  # Linux/Mac
netstat -ano | findstr 8111  # Windows
```

### 2. 数据库连接失败

**问题**：安装时提示"数据库连接失败"

**解决**：
- 确认 MySQL 服务正在运行
- 确认数据库已创建：`CREATE DATABASE dujiaoka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- 确认数据库用户有权限
- 如果 MySQL 在同一台服务器，地址填写主机IP，不要用 `localhost`
- 检查防火墙是否阻止了数据库端口

### 3. Redis 连接失败

**问题**：安装时提示"Redis 连接失败"

**解决**：
- 确认 Redis 服务正在运行
- 确认 Redis 配置允许远程连接（`bind 0.0.0.0`）
- 检查 Redis 密码是否正确
- 检查防火墙是否阻止了 Redis 端口

### 4. 文件上传失败

**问题**：后台上传图片失败

**解决**：
```bash
# 检查目录权限
docker compose exec web ls -la /app/public/uploads

# 手动设置权限
docker compose exec web chmod -R 777 /app/public/uploads
```

### 5. 队列任务不执行

**问题**：订单支付后不发送邮件

**解决**：
```bash
# 检查队列进程是否运行
docker compose exec web ps aux | grep queue

# 手动启动队列（测试）
docker compose exec web php artisan queue:work

# 查看队列日志
docker compose exec web cat /tmp/queue-work.log
```

### 6. 配置文件丢失

**问题**：容器重启后需要重新安装

**解决**：
- 确认 `./data/install.lock` 文件存在
- 确认 volume 挂载配置正确
- 检查容器内 `.env` 文件：`docker compose exec web cat /app/.env`

## 更新升级

### 更新镜像

```bash
# 1. 备份数据
docker cp dujiaoka:/app/.env ./backup/env.backup
docker compose down

# 2. 拉取新镜像
docker compose pull

# 3. 重新启动
docker compose up -d

# 4. 查看日志确认启动成功
docker compose logs -f web
```

### 数据库迁移

```bash
# 执行数据库迁移
docker compose exec web php artisan migrate --force
```

## 生产环境建议

### 1. 使用外部数据库和 Redis
- 不要在容器内运行数据库
- 使用专用的 MySQL 和 Redis 服务器
- 定期备份数据库

### 2. 配置 HTTPS
- 使用 Nginx 反向代理
- 配置 SSL 证书
- 修改 `.env` 中的 `ADMIN_HTTPS=true`

### 3. 优化性能
- 增加 PHP-FPM 进程数
- 配置 Redis 作为缓存和队列驱动
- 使用 CDN 加速静态资源

### 4. 安全加固
- 修改默认端口
- 使用强密码
- 定期更新镜像
- 配置防火墙规则

### 5. 监控和日志
- 定期查看应用日志
- 监控容器资源使用
- 配置日志轮转

## 技术支持

- 官方文档：https://github.com/assimon/dujiaoka/wiki
- 问题反馈：https://github.com/assimon/dujiaoka/issues

## 许可证

本项目采用 MIT 许可证
