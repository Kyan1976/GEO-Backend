<?php

namespace App\Services\GeoFlow;

use App\Models\ArticleDistribution;
use App\Models\DistributionChannel;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * 头条号 Publisher — 图文发布
 *
 * 使用头条号开放平台 API（抖音开放平台统一入口）
 * 流程：OAuth2.0授权 → access_token → 发布图文
 *
 * channel_config 需要字段：
 *   - toutiao_access_token  string  OAuth2授权后的access_token
 *   - toutiao_open_id       string  授权用户的open_id
 *
 * 注意：头条号开放平台需要先在 https://open.douyin.com 注册应用并获取授权
 * 首次使用需手动获取access_token（OAuth2流程），之后可refresh
 */
class ToutiaoApiPublisher implements DistributionPublisherInterface
{
    private const BASE = 'https://open.douyin.com';

    public function health(DistributionChannel $channel): array
    {
        $cfg = $this->config($channel);
        $token = $cfg['toutiao_access_token'] ?? '';
        $openId = $cfg['toutiao_open_id'] ?? '';

        if (!$token || !$openId) {
            return [
                'ok' => false,
                'channel_type' => 'toutiao_api',
                'error' => 'access_token 或 open_id 未配置，需先完成 OAuth2 授权',
            ];
        }

        // 调用获取用户信息接口验证token
        $resp = Http::withHeaders([
            'access-token' => $token,
        ])->get(self::BASE.'/api/douyin/v1/user/info/', [
            'open_id' => $openId,
        ]);

        $data = $resp->json();
        $ok = ($data['data'] ?? null) !== null;

        return [
            'ok' => $ok,
            'channel_type' => 'toutiao_api',
            'open_id' => $openId,
            'nickname' => $data['data']['nickname'] ?? '',
        ];
    }

    public function publish(ArticleDistribution $distribution, array $payload): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $article = $payload['article'] ?? [];
        $cfg = $this->config($channel);

        $token = $cfg['toutiao_access_token'] ?? '';
        $openId = $cfg['toutiao_open_id'] ?? '';

        if (!$token || !$openId) {
            throw new RuntimeException('头条号 access_token 或 open_id 未配置');
        }

        // 发布图文（创建文章）
        $content = (string) ($article['content_html'] ?? '');
        // 头条号文章内容支持HTML
        $title = (string) ($article['title'] ?? '');

        $resp = Http::withHeaders([
            'access-token' => $token,
        ])->post(self::BASE.'/api/douyin/v1/article/create/', [
            'open_id' => $openId,
            'title' => mb_substr($title, 0, 30), // 头条号标题最长30字
            'content' => $content,
            'cover_image' => (string) ($article['featured_image_url'] ?? ''),
        ]);

        $data = $resp->json();
        $code = $data['data']['error_code'] ?? ($data['data']['code'] ?? -1);
        if ($code !== 0 && !isset($data['data']['item_id'])) {
            $msg = $data['data']['description'] ?? json_encode($data, JSON_UNESCAPED_UNICODE);
            throw new RuntimeException('头条号发布失败: '.$msg);
        }

        $itemId = (string) ($data['data']['item_id'] ?? '');

        return [
            'remote_id' => $itemId,
            'remote_url' => 'https://www.toutiao.com/article/'.$itemId.'/',
            'remote_meta' => [
                'toutiao_item_id' => $itemId,
                'status' => 'published',
            ],
        ];
    }

    public function update(ArticleDistribution $distribution, array $payload): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $article = $payload['article'] ?? [];
        $cfg = $this->config($channel);

        $token = $cfg['toutiao_access_token'] ?? '';
        $openId = $cfg['toutiao_open_id'] ?? '';
        $itemId = $distribution->remote_id;

        if (!$itemId) {
            return $this->publish($distribution, $payload);
        }

        $resp = Http::withHeaders([
            'access-token' => $token,
        ])->post(self::BASE.'/api/douyin/v1/article/update/', [
            'open_id' => $openId,
            'item_id' => $itemId,
            'title' => mb_substr((string) ($article['title'] ?? ''), 0, 30),
            'content' => (string) ($article['content_html'] ?? ''),
        ]);

        $data = $resp->json();
        $code = $data['data']['error_code'] ?? ($data['data']['code'] ?? -1);
        if ($code !== 0) {
            throw new RuntimeException('头条号更新失败: '.($data['data']['description'] ?? 'unknown'));
        }

        return [
            'remote_id' => $itemId,
            'remote_url' => $distribution->remote_url,
            'remote_meta' => ['toutiao_item_id' => $itemId, 'status' => 'updated'],
        ];
    }

    public function delete(ArticleDistribution $distribution): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $cfg = $this->config($channel);
        $itemId = $distribution->remote_id;

        if (!$itemId) {
            return ['deleted' => true, 'remote_id' => null, 'remote_url' => null];
        }

        $token = $cfg['toutiao_access_token'] ?? '';
        $openId = $cfg['toutiao_open_id'] ?? '';

        Http::withHeaders(['access-token' => $token])
            ->post(self::BASE.'/api/douyin/v1/article/delete/', [
                'open_id' => $openId,
                'item_id' => $itemId,
            ]);

        return ['deleted' => true, 'remote_id' => $itemId, 'remote_url' => null];
    }

    public function syncSiteSettings(DistributionChannel $channel): array
    {
        return ['ok' => true, 'skipped' => true, 'reason' => 'toutiao_no_remote_settings'];
    }

    // ── 内部方法 ──

    private function channel(ArticleDistribution $dist): DistributionChannel
    {
        if (!$dist->channel instanceof DistributionChannel) {
            throw new RuntimeException('分发记录缺少头条号渠道。');
        }
        return $dist->channel;
    }

    private function config(DistributionChannel $ch): array
    {
        return $ch->resolvedChannelConfig();
    }
}
