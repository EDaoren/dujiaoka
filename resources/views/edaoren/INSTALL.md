# EDaoren 模板安装说明

## 模板文件清单

### 视图文件 (resources/views/edaoren/)
- ✅ layouts/default.blade.php - 默认布局
- ✅ layouts/seo.blade.php - SEO 优化布局
- ✅ layouts/_header.blade.php - 头部
- ✅ layouts/_nav.blade.php - 导航栏
- ✅ layouts/_footer.blade.php - 底部
- ✅ layouts/_script.blade.php - 脚本引用
- ✅ static_pages/home.blade.php - 首页
- ✅ static_pages/buy.blade.php - 购买页
- ✅ static_pages/bill.blade.php - 账单页
- ✅ static_pages/orderinfo.blade.php - 订单详情
- ✅ static_pages/qrpay.blade.php - 二维码支付
- ✅ static_pages/searchOrder.blade.php - 订单查询
- ✅ errors/error.blade.php - 错误页面
- ✅ README.md - 模板说明

### 资源文件 (public/assets/edaoren/)
- ✅ css/bootstrap.min.css - Bootstrap 框架
- ✅ css/base.css - 基础样式
- ✅ css/common.css - 通用样式（已重新设计）
- ✅ css/index.css - 主样式（已重新设计）
- ✅ js/jquery-3.6.0.min.js - jQuery
- ✅ js/bootstrap.min.js - Bootstrap JS
- ✅ js/bootstrap-input-spinner.js - 数量选择器
- ✅ js/clipboard.min.js - 剪贴板功能
- ✅ fonts/ - 图标字体文件

## 启用模板

### 方法一：通过管理后台（推荐）
1. 登录管理后台 `/admin`
2. 进入 "系统设置" → "网站设置"
3. 在 "模板选择" 中选择 "edaoren"
4. 保存设置

### 方法二：修改配置文件
编辑 `.env` 文件，找到或添加：
```
TEMPLATE=edaoren
```

或者修改 `config/dujiaoka.php` 中的模板配置。

## 配置建议

### Logo 设置
- **图片 Logo**: 建议尺寸 200x50px，PNG 格式，透明背景
- **文字 Logo**: 建议使用简短的品牌名称

### 商品图片
- **推荐尺寸**: 800x600px 或 16:9 比例
- **格式**: JPG 或 PNG
- **大小**: 建议不超过 500KB

### 公告设置
- 支持 HTML 格式
- 建议简洁明了，不超过 2-3 行

## 浏览器兼容性

本模板使用了现代 CSS 特性，建议使用以下浏览器：
- Chrome 76+
- Firefox 70+
- Safari 13+
- Edge 79+

## 注意事项

1. **无需修改后端代码**: 本模板完全兼容现有系统，只是视觉层面的改进
2. **保持原有功能**: 所有原有功能（支付、订单、优惠券等）完全保留
3. **响应式设计**: 自动适配手机、平板、电脑等各种设备
4. **性能优化**: 使用 CSS3 动画，性能优于 JavaScript 动画

## 自定义修改

如需自定义样式，可以修改以下文件：
- `public/assets/edaoren/css/index.css` - 主要样式
- `public/assets/edaoren/css/common.css` - 通用样式

### 修改主题色
在 `index.css` 中搜索以下颜色值并替换：
- 主色调: `#667eea`, `#764ba2`
- 成功色: `#11998e`, `#38ef7d`
- 警告色: `#f093fb`, `#f5576c`

## 技术支持

如遇到问题，请检查：
1. 浏览器是否支持现代 CSS 特性
2. 资源文件是否正确加载（检查浏览器控制台）
3. 缓存是否已清除

## 更新日志

### v1.0.0 (2026-01-25)
- ✅ 初始版本发布
- ✅ 现代化渐变设计
- ✅ 完整的动画效果
- ✅ 响应式布局
- ✅ 毛玻璃效果
- ✅ 悬停交互动画
