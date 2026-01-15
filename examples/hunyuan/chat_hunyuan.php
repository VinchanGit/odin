<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Odin\Api\Request\ChatCompletionRequest;
use Hyperf\Odin\Logger;
use Hyperf\Odin\Message\SystemMessage;
use Hyperf\Odin\Message\UserMessage;
use Hyperf\Odin\Model\OpenAIModel;

use function Hyperf\Support\env;

ClassLoader::init();

$container = ApplicationContext::setContainer(new Container((new DefinitionSourceFactory())()));

// 创建腾讯混元模型实例
// 腾讯混元 API 完全兼容 OpenAI 协议，因此直接使用 OpenAIModel
// 环境变量配置示例：
// HUNYUAN_API_KEY=your_api_key
// HUNYUAN_BASE_URL=https://api.hunyuan.cloud.tencent.com/v1
$model = new OpenAIModel(
    'hunyuan-standard',  // 模型名称，可选：hunyuan-standard, hunyuan-turbo, hunyuan-pro 等
    [
        'api_key' => env('HUNYUAN_API_KEY'),
        'base_url' => env('HUNYUAN_BASE_URL', 'https://api.hunyuan.cloud.tencent.com/v1'),
    ],
    new Logger(),
);

// 构建对话消息
$messages = [
    new SystemMessage('你是一个专业、友好的AI助手，擅长回答各种问题。'),
    new UserMessage('你好，请介绍一下腾讯混元大模型的特点。'),
];

$start = microtime(true);

// 使用非流式API调用
$request = new ChatCompletionRequest($messages, maxTokens: 2048);
$response = $model->chatWithRequest($request);

// 输出响应内容
$message = $response->getFirstChoice()->getMessage();
echo '助手回复：' . PHP_EOL;
echo $message->getContent() . PHP_EOL;
echo PHP_EOL;

// 输出使用统计
$usage = $response->getUsage();
if ($usage) {
    echo 'Token 使用情况：' . PHP_EOL;
    echo "- 提示词 Tokens: {$usage->getPromptTokens()}" . PHP_EOL;
    echo "- 完成 Tokens: {$usage->getCompletionTokens()}" . PHP_EOL;
    echo "- 总计 Tokens: {$usage->getTotalTokens()}" . PHP_EOL;
    echo PHP_EOL;
}

echo '耗时：' . round(microtime(true) - $start, 2) . ' 秒' . PHP_EOL;
