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
use Hyperf\Odin\Model\HunyuanModel;

use function Hyperf\Support\env;

ClassLoader::init();

$container = ApplicationContext::setContainer(new Container((new DefinitionSourceFactory())()));

// 创建腾讯混元模型实例
$model = new HunyuanModel(
    'hunyuan-standard',
    [
        'api_key' => env('HUNYUAN_API_KEY'),
        'base_url' => env('HUNYUAN_BASE_URL', 'https://api.hunyuan.cloud.tencent.com/v1'),
    ],
    new Logger(),
);

// 构建对话消息
$messages = [
    new SystemMessage('你是一个专业的技术顾问，能够清晰地解释复杂的技术概念。'),
    new UserMessage('请详细解释一下什么是大语言模型（LLM），以及它们的工作原理。'),
];

echo '开始流式响应...' . PHP_EOL . PHP_EOL;
echo '助手回复：' . PHP_EOL;

$start = microtime(true);

// 使用流式API调用
$request = new ChatCompletionRequest($messages, maxTokens: 2048);
$streamResponse = $model->chatStreamWithRequest($request);

// 逐块处理流式响应
foreach ($streamResponse->getStream() as $chunk) {
    $delta = $chunk->getFirstChoice()->getDelta();

    // 输出内容增量
    if ($delta && $delta->getContent()) {
        echo $delta->getContent();
        flush(); // 立即输出到屏幕
    }
}

echo PHP_EOL . PHP_EOL;

// 输出使用统计（如果流式响应包含）
$usage = $streamResponse->getUsage();
if ($usage) {
    echo 'Token 使用情况：' . PHP_EOL;
    echo "- 提示词 Tokens: {$usage->getPromptTokens()}" . PHP_EOL;
    echo "- 完成 Tokens: {$usage->getCompletionTokens()}" . PHP_EOL;
    echo "- 总计 Tokens: {$usage->getTotalTokens()}" . PHP_EOL;
    echo PHP_EOL;
}

echo '总耗时：' . round(microtime(true) - $start, 2) . ' 秒' . PHP_EOL;
