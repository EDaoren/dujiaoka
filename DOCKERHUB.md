# 独角数卡 (Dujiaoka) - Docker 镜像

[![GitHub](https://img.shields.io/badge/GitHub-EDaoren%2Fdujiaoka-blue)](https://github.com/EDaoren/dujiaoka)
[![License](https://img.shields.io/badge/license-MIT-green)](https://opensource.org/licenses/MIT)

开源式站长自动化售货解决方案 - 自动化 Docker 部署版本

## 📢 关于此镜像

本镜像基于 [assimon/dujiaoka](https://github.com/assimon/dujiaoka) 项目，专门优化了 Docker 部署体验：

- ✅ **零配置启动**：容器启动时自动初始化数据库，无需访问 `/install` 页面
- ✅ **配置持久化**：容器重启后自动恢复配置，无需重新安装
- ✅ **环境变量管理**：所有配置通过环境变量统一管理

## 🚀 快速开始

### 前置要求
- 外部 MySQL 数据库（5.6+）
- 外部 Redis 服务

### 使用步骤

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
    image: edaorenchan/dujiaoka:latest
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
      # APP_KEY 用于加密，建议修改为你自己的密钥（必须保留 base64: 前缀）
      APP_KEY: "base64:hDVkYhfkUjaePiaI1tcBT7G8bh2A8RQxwWIGkq7BO18="

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

**4. 访问应用**
- 前台：`http://localhost:8111`
- 后台：`http://localhost:8111/admin`
- 默认账号：`admin` / `admin`

## 📝 环境变量说明

### 重要提示

**APP_KEY 说明**

加密数据用的，部署后就不要改，否则加密数据无法解密

```bash
openssl rand -base64 32  # 生成随机密钥
# 添加 base64: 前缀：base64:xK3j8mN9pL2qR5sT7vU1wX4yZ6aB8cD0
```
⚠️ 必须保留 `base64:` 前缀！

### 环境变量列表

| 变量名 | 说明 | 默认值 |
|--------|------|--------|
| `APP_NAME` | 应用名称 | 独角数卡 |
| `APP_URL` | 应用地址 | http://localhost:8111 |
| `APP_ENV` | 运行环境 | production |
| `APP_DEBUG` | 调试模式 | false |
| `APP_KEY` | 加密密钥（必须保留 base64: 前缀） | base64:xK3j8mN9... |
| `APP_HTTPS` | 是否启用 HTTPS | false |
| `DB_HOST` | MySQL 主机地址 | **必填** |
| `DB_PORT` | MySQL 端口 | 3306 |
| `DB_DATABASE` | 数据库名 | **必填** |
| `DB_USERNAME` | 数据库用户名 | **必填** |
| `DB_PASSWORD` | 数据库密码 | **必填** |
| `REDIS_HOST` | Redis 主机地址 | **必填** |
| `REDIS_PORT` | Redis 端口 | 6379 |
| `REDIS_PASSWORD` | Redis 密码 | (可选) |
| `ADMIN_ROUTE_PREFIX` | 后台路径前缀 | admin |
| `ADMIN_LANGUAGE` | 后台语言 | zh_CN |

## 🔧 工作原理

容器启动时会自动执行以下操作：
1. 从环境变量生成 `.env` 配置文件
2. 检测数据库是否已初始化
3. 如果是首次启动，自动导入 SQL 并创建初始数据
4. 如果是重启，自动恢复配置，跳过安装步骤
5. 创建 `install.lock` 锁定安装状态

**完全不需要访问 `/install` 页面！**

## 📚 完整文档

- [GitHub 仓库](https://github.com/EDaoren/dujiaoka)
- [原项目文档](https://github.com/assimon/dujiaoka/wiki)
- [支付接口配置](https://github.com/assimon/dujiaoka/wiki/problems#各支付对应配置)
- [常见问题](https://github.com/assimon/dujiaoka/wiki/problems)

## 📄 License

本项目遵循 [MIT license](https://opensource.org/licenses/MIT)

感谢原作者 [assimon](https://github.com/assimon) 的开源贡献！
