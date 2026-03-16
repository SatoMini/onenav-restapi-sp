## OneNav REST API Meta Fields Support 插件
### 插件及相关使用说明案例

本插件主要目的是达成：OpenClaw + WordPress + OneNav 主题：打造 AI 自动化网址发布的导航网站

参考文章说明：https://warpnav.com/openclaw-wp-onenav

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

计算机科学

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
