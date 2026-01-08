# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**独角数卡 (Dujiaoka)** is an open-source automated digital goods selling platform built with Laravel. It's designed for merchants to sell digital products like game cards, software licenses, and other virtual goods with automated delivery.

**Technology Stack:**
- Laravel 6.x (PHP Framework)
- Dcat Admin (Admin Panel)
- MySQL/MariaDB (Database)
- Redis (Cache & Queue)
- Bootstrap (Frontend UI)
- Laravel Mix (Asset Compilation)

## Development Commands

### Environment Setup
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database installation (via web interface)
# Visit /install in browser after setup
```

### Development Workflow
```bash
# Start development server
php artisan serve

# Compile assets for development
npm run dev

# Watch for changes and recompile
npm run watch

# Compile for production
npm run production

# Run queue worker (required for order processing)
php artisan queue:work

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Testing
```bash
# Run all tests
php artisan test
# or
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Feature
./vendor/bin/phpunit --testsuite=Unit
```

## Architecture Overview

### Core Components

**Admin System (`app/Admin/`)**
- Built on Dcat Admin framework
- Controllers: Manage goods, orders, payments, coupons, email templates
- Charts: Dashboard analytics and sales metrics
- Forms: System settings, email testing, bulk imports
- Repositories: Data access layer for admin operations

**Frontend Controllers (`app/Http/Controllers/`)**
- `Home/HomeController`: Main storefront, product display, order creation
- `Home/OrderController`: Order management, search, status checking
- `Pay/*Controller`: Payment gateway integrations (Alipay, WeChat, PayPal, Stripe, etc.)

**Models (`app/Models/`)**
- `Goods`: Product catalog management
- `Order`: Order processing and tracking
- `Carmis`: Digital goods inventory (cards/keys)
- `Coupon`: Discount code system
- `Pay`: Payment method configuration

**Payment System**
- Supports 10+ payment gateways
- Unified payment interface in `PayController`
- Individual gateway controllers in `app/Http/Controllers/Pay/`
- Webhook handling for payment notifications

**Template System**
- Multiple frontend themes: `unicorn` (official), `luna`, `hyper`
- Templates located in `resources/views/{theme}/`
- Theme selection via admin panel

### Key Middleware
- `DujiaoBoot`: Application initialization
- `InstallCheck`: Installation status verification
- `PayGateWay`: Payment processing validation

### Configuration
- Main config: `config/dujiaoka.php` (version, templates, languages)
- Environment: `.env` file with database, Redis, payment settings
- Admin settings stored in database, managed via web interface

## Database Structure

The application uses a single SQL installation file (`database/sql/install.sql`) rather than Laravel migrations. Key tables include:
- `goods`: Product catalog
- `orders`: Order records
- `carmis`: Digital inventory
- `coupons`: Discount codes
- `pays`: Payment methods
- `emailtpls`: Email templates

## Payment Integration

Each payment method has its own controller with standardized methods:
- `gateway()`: Initialize payment
- `notifyUrl()`: Handle payment webhooks
- `returnUrl()`: Handle payment returns (where applicable)

Supported gateways: Alipay, WeChat Pay, PayPal, Stripe, Coinbase, and various Chinese payment providers.

## Frontend Templates

Templates are self-contained in `resources/views/{template}/`:
- Each template has its own assets and layout
- Bootstrap-based responsive design
- Customizable via admin panel settings

## Queue System

Uses Redis queues for:
- Email notifications (`app/Jobs/`)
- Payment processing
- Order fulfillment
- API webhooks

Requires `php artisan queue:work` to be running in production.

## Development Notes

- Admin panel accessible at `/admin` (configurable via `ADMIN_ROUTE_PREFIX`)
- Default admin credentials: `admin/admin`
- Supports multi-language (Chinese Simplified/Traditional)
- Requires Redis for optimal performance
- Uses Supervisor for queue management in production
- File uploads and logs stored in `storage/`

## Production Deployment

Requires:
- PHP 7.4+ with extensions: fileinfo, redis, bcmath, gd, zip, curl
- MySQL 5.6+ or MariaDB
- Redis server
- Nginx/Apache with proper Laravel configuration
- Supervisor for queue processing
- SSL certificate (recommended)

The application includes Docker support via `Dockerfile` and `docker-compose.yml`.