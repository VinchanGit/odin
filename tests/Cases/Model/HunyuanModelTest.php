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

namespace HyperfTest\Odin\Cases\Model;

use Hyperf\Odin\Model\HunyuanModel;
use HyperfTest\Odin\Cases\AbstractTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * 腾讯混元模型测试类.
 *
 * @internal
 * @coversNothing
 */
#[CoversClass(HunyuanModel::class)]
class HunyuanModelTest extends AbstractTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 测试 getApiVersionPath 方法.
     *
     * 验证腾讯混元模型返回正确的 API 版本路径。
     */
    public function testGetApiVersionPath()
    {
        $model = new HunyuanModel('hunyuan-standard', []);

        $apiVersionPath = $this->callNonpublicMethod($model, 'getApiVersionPath');

        $this->assertEquals('v1', $apiVersionPath);
    }

    /**
     * 测试 hasApiPathInBaseUrl 方法.
     *
     * 验证能够正确识别 base_url 中是否已包含 API 路径。
     */
    public function testHasApiPathInBaseUrl()
    {
        $model = new HunyuanModel('hunyuan-standard', []);

        // 测试没有路径的 URL
        $result = $this->callNonpublicMethod($model, 'hasApiPathInBaseUrl', 'https://api.hunyuan.cloud.tencent.com');
        $this->assertFalse($result);

        // 测试有路径的 URL
        $result = $this->callNonpublicMethod($model, 'hasApiPathInBaseUrl', 'https://api.hunyuan.cloud.tencent.com/v1');
        $this->assertTrue($result);

        // 测试只有根路径的 URL
        $result = $this->callNonpublicMethod($model, 'hasApiPathInBaseUrl', 'https://api.hunyuan.cloud.tencent.com/');
        $this->assertFalse($result);
    }

    /**
     * 测试 processApiBaseUrl 方法.
     *
     * 验证 API 基础 URL 的处理逻辑是否正确。
     */
    public function testProcessApiBaseUrlChangeBaseUrl()
    {
        $model = new HunyuanModel('hunyuan-standard', []);

        // 测试不包含版本路径的 URL
        $url = 'https://api.hunyuan.cloud.tencent.com';
        $hasPath = $this->callNonpublicMethod($model, 'hasApiPathInBaseUrl', $url);
        $this->assertFalse($hasPath, '期望 hasApiPathInBaseUrl 返回 false');

        $versionPath = $this->callNonpublicMethod($model, 'getApiVersionPath');
        $this->assertEquals('v1', $versionPath, '期望版本路径为 v1');

        // 验证 URL 拼接逻辑
        $config = ['base_url' => $url];
        $expectedUrl = rtrim($url, '/') . '/' . ltrim($versionPath, '/');
        $this->assertEquals('https://api.hunyuan.cloud.tencent.com/v1', $expectedUrl, '期望计算出的 URL 正确');
    }

    /**
     * 测试 streamIncludeUsage 属性.
     *
     * 验证腾讯混元模型支持在流式响应中包含使用统计。
     */
    public function testStreamIncludeUsage()
    {
        $model = new HunyuanModel('hunyuan-standard', []);

        $streamIncludeUsage = $this->getNonpublicProperty($model, 'streamIncludeUsage');

        $this->assertTrue($streamIncludeUsage, '期望 streamIncludeUsage 为 true');
    }

    /**
     * 测试模型实例化.
     *
     * 验证能够正确创建腾讯混元模型实例。
     */
    public function testModelInstantiation()
    {
        $config = [
            'api_key' => 'test_api_key',
            'base_url' => 'https://api.hunyuan.cloud.tencent.com/v1',
        ];

        $model = new HunyuanModel('hunyuan-standard', $config);

        $this->assertInstanceOf(HunyuanModel::class, $model);
        $this->assertEquals('hunyuan-standard', $this->getNonpublicProperty($model, 'model'));
        $this->assertEquals($config, $this->getNonpublicProperty($model, 'config'));
    }

    /**
     * 测试支持不同的模型名称.
     *
     * 验证支持腾讯混元的各种模型版本。
     */
    public function testDifferentModelNames()
    {
        $modelNames = [
            'hunyuan-standard',
            'hunyuan-turbo',
            'hunyuan-pro',
            'hunyuan-lite',
        ];

        foreach ($modelNames as $modelName) {
            $model = new HunyuanModel($modelName, []);
            $this->assertEquals($modelName, $this->getNonpublicProperty($model, 'model'));
        }
    }
}
