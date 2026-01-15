# 腾讯混元模型示例

本目录包含腾讯混元大语言模型的使用示例。

> **注意**：腾讯混元 API 完全兼容 OpenAI 协议，所有示例都使用 `OpenAIModel` 类，只需配置不同的 `base_url` 即可。

## 前置条件

1. **获取 API 密钥**
   - 访问 [腾讯云控制台](https://console.cloud.tencent.com/)
   - 开通混元大模型服务
   - 创建并获取 API 密钥

2. **配置环境变量**
   ```bash
   cp .env.example .env
   # 编辑 .env 文件，填入您的 API 密钥
   ```

## 关于 OpenAI 兼容性

腾讯混元模型采用 OpenAI 兼容的 API 协议，因此可以直接使用 `OpenAIModel` 类，无需单独的 `HunyuanModel` 类。

**使用方法：**
```php
use Hyperf\Odin\Model\OpenAIModel;

$model = new OpenAIModel(
    'hunyuan-standard',
    [
        'api_key' => env('HUNYUAN_API_KEY'),
        'base_url' => 'https://api.hunyuan.cloud.tencent.com/v1',
    ],
    new Logger()
);
```

## 示例文件说明

### 1. chat_hunyuan.php
基础对话示例，展示如何使用腾讯混元进行简单的对话交互。

**运行方式：**
```bash
php chat_hunyuan.php
```

**功能特性：**
- 同步对话请求
- 完整响应输出
- Token 使用统计
- 耗时统计

### 2. chat_hunyuan_stream.php
流式响应示例，展示如何实时获取模型的响应内容。

**运行方式：**
```bash
php chat_hunyuan_stream.php
```

**功能特性：**
- 流式响应处理
- 实时内容输出
- 逐块内容显示
- 响应统计信息

### 3. hunyuan_tool.php
底层工具调用示例，展示工具调用的完整流程（两轮对话）。

**运行方式：**
```bash
php hunyuan_tool.php
```

**功能特性：**
- 底层工具调用流程
- 工具定义和参数验证
- 工具执行结果处理
- 两轮对话机制
- 详细的调试输出

### 4. hunyuan_tool_stream.php
流式工具调用示例，展示流式响应中的工具调用处理。

**运行方式：**
```bash
php hunyuan_tool_stream.php
```

**功能特性：**
- 流式工具调用
- 实时响应输出
- 工具调用检测
- 两轮流式对话
- 完整的流程演示


 ## 示例对比

### 工具调用示例对比

| 特性 | hunyuan_tool.php | hunyuan_tool_stream.php |
|-----|------------------|-------------------------|
| 响应方式 | 同步 | 流式 |
| 封装级别 | 底层 | 底层 |
| 学习难度 | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| 灵活性 | 高 | 高 |
| 适合场景 | 学习工具调用机制 | 实时交互应用 |
| 代码复杂度 | 中 | 高 |
| 对标示例 | gemini_tool.php | gemini_tool_stream.php |

**学习建议**：
1. 先学习 `hunyuan_tool.php` 理解工具调用的基本流程
2. 然后学习 `hunyuan_tool_stream.php` 了解流式处理
3. 对比 Gemini 示例，理解不同模型的工具调用差异

## 支持的模型

腾讯混元提供多个模型版本：

| 模型名称 | 特点 | 适用场景 |
|---------|------|---------|
| hunyuan-standard | 标准版本 | 通用对话、内容生成 |
| hunyuan-turbo | 快速版本 | 实时交互、客服系统 |
| hunyuan-pro | 专业版本 | 复杂推理、专业领域 |
| hunyuan-lite | 轻量版本 | 简单问答、快速响应 |

## 功能支持

- ✅ 对话生成（Chat Completions）
- ✅ 流式响应（Streaming）
- ✅ 函数调用（Function Calling）
- ✅ 工具使用（Tool Use）
- ✅ 系统提示词（System Message）
- ✅ 多轮对话（Multi-turn Conversation）
- ✅ 中文优化

## 常见问题

### 1. API 密钥错误
**错误信息：** `API密钥不能为空` 或 `Invalid API key`

**解决方案：**
- 检查 `.env` 文件中的 `HUNYUAN_API_KEY` 是否正确配置
- 确认 API 密钥是否有效
- 检查 API 密钥是否有权限访问混元服务

### 2. 网络连接问题
**错误信息：** `Connection timeout` 或 `Network error`

**解决方案：**
- 检查网络连接是否正常
- 确认防火墙是否允许访问腾讯云服务
- 尝试使用代理（如需要）

### 3. 模型不存在
**错误信息：** `Model not found` 或 `Invalid model`

**解决方案：**
- 检查模型名称是否正确
- 确认您的账户是否有权限使用该模型
- 参考文档使用支持的模型名称

## 更多资源

- [腾讯混元官方文档](https://cloud.tencent.com/document/product/1729)
- [Odin 框架文档](../../doc/user-guide-cn/04-model-providers.md)
- [API 参考](../../doc/user-guide-cn/03-api-reference.md)

## 技术支持

如有问题，请：
1. 查看 [常见问题解答](../../doc/user-guide-cn/10-faq.md)
2. 提交 [GitHub Issue](https://github.com/hyperf/odin/issues)
3. 加入社区讨论群

