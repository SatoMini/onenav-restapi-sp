## OneNav REST API Meta Fields Support 插件
### 插件及相关使用说明案例

本插件主要目的是达成：OpenClaw + WordPress + OneNav 主题：打造 AI 自动化网址发布的导航网站

参考文章说明初始篇：https://warpnav.com/openclaw-wp-onenav

进阶篇：https://warpnav.com/openclaw-onenav-rest-api

### 1、这是个什么插件？

这是本人经过多轮测试总结，由我的Openclaw开发的一个适配OneNav主题使用REST API发布文章写入 OneNav 主题特殊字段的插件。

### 2、为什么需要这个插件？

OneNav 主题使用大量\*\*自定义字段\*\*（Custom Fields）存储站点信息，例如：  
– 站点 URL  
– 一句话简介  
– 站点预览图  
– Favicon 图标  
– SEO 关键词

\*\*问题：\*\* WordPress REST API 默认不暴露这些字段，导致无法通过 API 读取或写入。

\*\*解决方案：\*\* 安装 OneNav REST API Meta Fields Support 插件，扩展 REST API 的功能。

### 3、插件安装

1\. 将 \`onenav-restapi-sp\` 插件文件夹上传到 \`wp-content/plugins/\` 目录  
2\. 在 WordPress 后台”插件”页面启用该插件

### 4、 插件功能

安装后，你将能够：

| 操作 | 说明 |  
|——|——|  
| 读取站点信息 | 通过 REST API 获取 OneNav 自定义字段 |  
| 写入站点信息 | 通过 REST API 更新 OneNav 自定义字段 |  
| 批量管理 | 一次性读取/写入多个字段 |

### 5、 验证插件是否生效

访问以下自己网站的 URL：your-domain.com/wp-json/wp/v2/posts/【文章ID】

例：warpnav.com/wp-json/wp/v2/posts/1565

如果返回包含 \`onav\_meta\` 等字段的数据，说明插件已生效。

## 🚀 版本 v2.1.1 (最新修复版)
### 核心修复 (Critical Fix)
- 修复分类法 Meta 写入失效问题 ：修复了分类和标签（Taxonomy）自定义字段无法通过 REST API 正确保存的严重 Bug。
  - 原因 ：OneNav 主题将分类 SEO 和显示字段以序列化数组形式存储在 term_io_seo 和 term_io_{taxonomy} 中，而非独立字段。
  - 修复 ：重写了 update_callback 和 get_callback ，实现了对主题序列化数据结构的完美兼容。现在通过 API 修改标签的 SEO 标题、关键词等信息，前端将即时生效。
## 🛠️ 版本 v2.1.0 (功能增强版)
### 新增功能 (New Features)
- 后台控制面板 ：在 WordPress 后台 设置 (Settings) -> OneNav REST API 增加了独立的设置页面。
- 动态开关控制 ：
  - 支持按“文章类型”和“分类法”维度分别控制 REST API 的暴露。
  - 允许管理员根据需求，精细化开启或关闭特定模块（如只开启 sites 而关闭 book ）的 API 读写权限。
  - 默认状态为全部开启，确保平滑升级。
