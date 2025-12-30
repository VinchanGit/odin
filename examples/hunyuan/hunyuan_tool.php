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
! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 2));

require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Odin\Logger;
use Hyperf\Odin\Message\AssistantMessage;
use Hyperf\Odin\Message\SystemMessage;
use Hyperf\Odin\Message\ToolMessage;
use Hyperf\Odin\Message\UserMessage;
use Hyperf\Odin\Model\HunyuanModel;
use Hyperf\Odin\Model\ModelOptions;
use Hyperf\Odin\Tool\Definition\ToolDefinition;
use Hyperf\Odin\Tool\Definition\ToolParameters;

use function Hyperf\Support\env;

ClassLoader::init();

$container = ApplicationContext::setContainer(new Container((new DefinitionSourceFactory())()));

// 创建腾讯混元模型实例
// 使用混元标准版模型
$model = new HunyuanModel(
    'hunyuan-standard',
    [
        'api_key' => env('HUNYUAN_API_KEY'),
        'base_url' => env('HUNYUAN_BASE_URL', 'https://api.hunyuan.cloud.tencent.com/v1'),
    ],
    new Logger(),
);
$model->setModelOptions(new ModelOptions([
    'function_call' => true,
]));

echo '=== 腾讯混元工具调用测试 ===' . PHP_EOL;
echo '支持函数调用功能' . PHP_EOL . PHP_EOL;

// 定义天气查询工具
$weatherTool = new ToolDefinition(
    name: 'weather',
    description: '查询指定城市的天气信息。当用户询问天气时，必须使用此工具来获取天气数据。',
    parameters: ToolParameters::fromArray([
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => '要查询天气的城市名称，例如：北京、上海、广州、深圳',
            ],
        ],
        'required' => ['city'],
    ]),
    toolHandler: function ($params) {
        $city = $params['city'];
        // 模拟天气数据
        $weatherData = [
            '北京' => ['temperature' => '15°C', 'condition' => '晴朗', 'humidity' => '45%'],
            '上海' => ['temperature' => '20°C', 'condition' => '多云', 'humidity' => '60%'],
            '广州' => ['temperature' => '25°C', 'condition' => '阵雨', 'humidity' => '75%'],
            '深圳' => ['temperature' => '24°C', 'condition' => '晴朗', 'humidity' => '65%'],
        ];

        if (isset($weatherData[$city])) {
            return $weatherData[$city];
        }
        return ['error' => '没有找到该城市的天气信息'];
    }
);

$toolMessages = [
    new SystemMessage('你是一位有用的天气助手。当用户询问任何城市的天气信息时，你必须使用 weather 工具来查询天气数据，然后根据查询结果回答用户。'),
    new UserMessage('请查询深圳的天气。'),
];

$start = microtime(true);

// 第一轮：使用工具进行 API 调用
echo '>>> 第一轮对话：请求天气信息' . PHP_EOL;
$response = $model->chat($toolMessages, 0.7, 0, [], [$weatherTool]);

// 输出完整响应
$message = $response->getFirstChoice()->getMessage();
if ($message instanceof AssistantMessage) {
    echo '响应内容: ' . ($message->getContent() ?? '无内容，可能是工具调用') . PHP_EOL;

    // 检查是否有工具调用
    $toolCalls = $message->getToolCalls();
    if (! empty($toolCalls)) {
        echo PHP_EOL . '检测到工具调用:' . PHP_EOL;
        foreach ($toolCalls as $toolCall) {
            echo '  - 工具ID: ' . $toolCall->getId() . PHP_EOL;
            echo '  - 工具名称: ' . $toolCall->getName() . PHP_EOL;
            echo '  - 工具参数: ' . json_encode($toolCall->getArguments(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }

        // 模拟工具执行
        echo PHP_EOL . '>>> 执行工具...' . PHP_EOL;

        // 将助手的工具调用消息添加到对话中
        $toolMessages[] = $message;

        // 为每个工具调用创建工具响应消息
        foreach ($toolCalls as $toolCall) {
            // 执行实际的工具处理器
            $toolResult = $weatherTool->getToolHandler()($toolCall->getArguments());

            echo '  工具执行结果: ' . json_encode($toolResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

            // 创建工具响应消息
            $toolContent = json_encode($toolResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $toolResponseMessage = new ToolMessage(
                $toolContent,
                $toolCall->getId(),
                $weatherTool->getName(),
                $toolCall->getArguments()
            );
            $toolMessages[] = $toolResponseMessage; // 添加工具响应
        }

        // 第二轮：继续对话，包含所有工具响应
        echo PHP_EOL . '>>> 第二轮对话：根据工具结果生成回复' . PHP_EOL;
        $continueResponse = $model->chat($toolMessages, 0.7, 0, [], [$weatherTool]);
        $continueMessage = $continueResponse->getFirstChoice()->getMessage();
        if ($continueMessage instanceof AssistantMessage) {
            echo '助手最终回复:' . PHP_EOL;
            echo $continueMessage->getContent() . PHP_EOL;
        }

        // 输出使用统计
        $usage = $continueResponse->getUsage();
        if ($usage) {
            echo PHP_EOL . 'Token 使用情况（第二轮）:' . PHP_EOL;
            echo '  - 提示词 Tokens: ' . $usage->getPromptTokens() . PHP_EOL;
            echo '  - 完成 Tokens: ' . $usage->getCompletionTokens() . PHP_EOL;
            echo '  - 总计 Tokens: ' . $usage->getTotalTokens() . PHP_EOL;
        }
    } else {
        echo PHP_EOL . '未检测到工具调用' . PHP_EOL;
        echo '这可能意味着：' . PHP_EOL;
        echo '  1. 模型直接回答了问题，没有使用工具' . PHP_EOL;
        echo '  2. 或者工具定义不够明确' . PHP_EOL;
    }
}

echo PHP_EOL . '总耗时：' . round(microtime(true) - $start, 2) . ' 秒' . PHP_EOL;
