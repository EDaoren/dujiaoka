<p align="center"><img src="https://i.loli.net/2020/04/07/nAzjDJlX7oc5qEw.png" width="400"></p>

<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/assimon/dujiaoka/releases/tag/2.0.4"><img src="https://img.shields.io/badge/version-2.0.4-red" alt="version 2.0.4"></a>
<a href="https://www.php.net/releases/7_4_0.php"><img src="https://img.shields.io/badge/PHP-7.4-lightgrey" alt="php74"></a>
<a href="https://hub.docker.com/r/edaoren/dujiaoka"><img src="https://img.shields.io/docker/pulls/edaoren/dujiaoka" alt="Docker Pulls"></a>
</p>

## 📢 Fork 说明

本仓库 Fork 自 [assimon/dujiaoka](https://github.com/assimon/dujiaoka)

**原项目完整说明请查看：** [原版 README](https://github.com/assimon/dujiaoka/blob/master/README.md)

## ✨ 本 Fork 的改进

### Docker 自动化部署
- ✅ **零配置启动**：容器启动时自动初始化数据库，无需访问 `/install` 页面
- ✅ **配置持久化**：容器重启后自动恢复配置，无需重新安装
- ✅ **环境变量管理**：所有配置通过 `docker-compose.yml` 统一管理
- ✅ **增加支付方式**：增加了 linux.do ldc 积分支付

## 🚀 Docker 快速开始

### 方式一：使用 Docker Hub 镜像（推荐，最简单）

**前置要求：**
- 外部 MySQL 数据库（5.6+）
- 外部 Redis 服务

**1. 创建数据库**
```sql
CREATE DATABASE dujiaoka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**2. 创建项目目录并保存配置**
```bash
mkdir dujiaoka && cd dujiaoka
nano docker-compose.yml  # 或使用你喜欢的编辑器
```

将以下内容粘贴到 `docker-compose.yml`（**记得修改数据库和 Redis 配置**）：

```yaml
services:
  web:
    image: edaorenchan/dujiaoka:latest  # 使用 Docker Hub 镜像
    container_name: dujiaoka
    ports:
      - "8111:80"
    volumes:
      - ./data/uploads:/app/public/uploads
    environment:
      # Web 服务器配置
      WEB_DOCUMENT_ROOT: "/app/public"
      TZ: Asia/Shanghai

      # 应用配置
      APP_NAME: "独角数卡"
      APP_URL: "http://localhost:8111"  # 改成你的域名
      APP_ENV: "production"
      APP_DEBUG: "false"
      APP_HTTPS: "false"  # 如果配置 HTTPS 域名，改为 true

      # 数据库配置（请修改为你的实际配置）
      DB_HOST: "your_mysql_host"
      DB_PORT: "3306"
      DB_DATABASE: "dujiaoka"
      DB_USERNAME: "your_mysql_user"
      DB_PASSWORD: "your_mysql_password"

      # Redis 配置（请修改为你的实际配置）
      REDIS_HOST: "your_redis_host"
      REDIS_PORT: "6379"
      REDIS_PASSWORD: ""

      # 后台配置
      ADMIN_ROUTE_PREFIX: "admin"
      ADMIN_LANGUAGE: "zh_CN"

    tty: true
    restart: always
    networks:
      - dujiaoka

networks:
  dujiaoka:
    driver: bridge
```

**3. 启动容器**
```bash
docker-compose up -d
```

**4. 访问**
- 前台：`http://localhost:8111`
- 后台：`http://localhost:8111/admin` (账号：`admin` / 密码：`admin`)

---

### 方式二：从源码构建

**1. 克隆并配置**
```bash
git clone https://github.com/EDaoren/dujiaoka.git
cd dujiaoka
# 编辑 docker-compose.yml，修改数据库和 Redis 配置
```

**2. 在 MySQL 中创建空数据库**
```sql
CREATE DATABASE dujiaoka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. 启动容器（会自动初始化）**
```bash
docker-compose build
docker-compose up -d
```

**4. 访问**
- 前台：`http://localhost:8111`
- 后台：`http://localhost:8111/admin` (账号：`admin` / 密码：`admin`)

## 📝 配置说明

修改 `docker-compose.yml` 中的环境变量：
```yaml
environment:
  APP_URL: "http://your-domain.com"  # 你的域名
  DB_HOST: "your_mysql_host"         # MySQL 地址
  DB_DATABASE: "dujiaoka"
  DB_USERNAME: "your_mysql_user"
  DB_PASSWORD: "your_mysql_password"
  REDIS_HOST: "your_redis_host"      # Redis 地址
```

## 📚 更多文档

- [原项目完整文档](https://github.com/assimon/dujiaoka/wiki)
- [支付接口配置](https://github.com/assimon/dujiaoka/wiki/problems#各支付对应配置)
- [常见问题](https://github.com/assimon/dujiaoka/wiki/problems)

## 📄 License

本项目遵循原项目的 [MIT license](https://opensource.org/licenses/MIT)

感谢原作者 [assimon](https://github.com/assimon) 的开源贡献！
