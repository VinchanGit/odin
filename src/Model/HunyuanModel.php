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

namespace Hyperf\Odin\Model;

use Hyperf\Odin\Contract\Api\ClientInterface;
use Hyperf\Odin\Factory\ClientFactory;

/**
 * 腾讯混元模型实现.
 *
 * 腾讯混元（Tencent Hunyuan）是腾讯云推出的大语言模型服务，
 * 提供兼容 OpenAI API 格式的接口，支持对话、工具调用等功能。
 *
 * 使用示例：
 * ```php
 * $model = new HunyuanModel(
 *     'hunyuan-standard',
 *     [
 *         'api_key' => env('HUNYUAN_API_KEY'),
 *         'base_url' => env('HUNYUAN_BASE_URL', 'https://api.hunyuan.cloud.tencent.com/v1'),
 *     ],
 *     new Logger(),
 * );
 * ```
 */
class HunyuanModel extends AbstractModel
{
    /**
     * 混元模型支持在流式响应中包含使用统计信息.
     */
    protected bool $streamIncludeUsage = true;

    /**
     * 获取腾讯混元客户端实例.
     *
     * 混元 API 兼容 OpenAI API 格式，因此可以直接复用 OpenAI 客户端实现。
     */
    protected function getClient(): ClientInterface
    {
        // 处理API基础URL，确保包含正确的版本路径
        $config = $this->config;
        $this->processApiBaseUrl($config);

        // 使用ClientFactory创建OpenAI兼容客户端
        return ClientFactory::createOpenAIClient(
            $config,
            $this->getApiRequestOptions(),
            $this->logger
        );
    }

    /**
     * 获取API版本路径.
     *
     * 腾讯混元的API版本路径为 v1，与 OpenAI 保持一致。
     * 如果配置的 base_url 已包含版本路径，此方法返回空字符串。
     */
    protected function getApiVersionPath(): string
    {
        return 'v1';
    }
}
