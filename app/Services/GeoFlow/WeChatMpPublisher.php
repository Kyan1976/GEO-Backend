<?php

namespace App\Services\GeoFlow;

use App\Models\ArticleDistribution;
use App\Models\DistributionChannel;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * 微信公众号 Publisher — 创建草稿
 *
 * 流程：AppID+AppSecret → access_token → 上传图文内的图片(可选) → 新增草稿
 * 群发需要人工在公众号后台操作（微信限制）
 *
 * channel_config 需要字段：
 *   - wechat_app_id     string  服务号AppID
 *   - wechat_app_secret string  服务号AppSecret
 */
class WeChatMpPublisher implements DistributionPublisherInterface
{
    private const BASE = 'https://api.weixin.qq.com/cgi-bin';

    public function health(DistributionChannel $channel): array
    {
        $token = $this->accessToken($channel);
        $resp = Http::get(self::BASE.'/getcallbackip', ['access_token' => $token]);
        $data = $resp->json();

        return [
            'ok' => isset($data['ip_list']),
            'channel_type' => 'wechat_mp',
            'app_id' => $this->config($channel)['wechat_app_id'] ?? '',
            'has_token' => !empty($token),
        ];
    }

    public function publish(ArticleDistribution $distribution, array $payload): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $article = $payload['article'] ?? [];

        $token = $this->accessToken($channel);

        // 上传文章内的图片到微信素材（获取微信内部URL）
        $content = (string) ($article['content_html'] ?? '');
        $thumbMediaId = $this->uploadThumb($channel, $token, $article);

        // 新增草稿
        $draftResp = Http::post(self::BASE.'/draft/add?access_token='.$token, [
            'articles' => [
                [
                    'title'              => (string) ($article['title'] ?? ''),
                    'author'             => (string) ($article['author'] ?? '斓姿GEO Backend'),
                    'digest'             => (string) ($article['excerpt'] ?? ''),
                    'content'            => $content,
                    'thumb_media_id'     => $thumbMediaId,
                    'need_open_comment'  => 1,
                    'only_fans_can_comment' => 0,
                ],
            ],
        ]);

        $result = $draftResp->json();
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            throw new RuntimeException('微信草稿创建失败: '.$result['errmsg'].' (errcode='.$result['errcode'].')');
        }

        $mediaId = (string) ($result['media_id'] ?? '');

        return [
            'remote_id' => $mediaId,
            'remote_url' => '',
            'remote_meta' => [
                'wechat_media_id' => $mediaId,
                'status' => 'draft',
                'note' => '草稿已创建，需手动在公众号后台群发',
            ],
        ];
    }

    public function update(ArticleDistribution $distribution, array $payload): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $article = $payload['article'] ?? [];
        $mediaId = $distribution->remote_id;

        if (!$mediaId) {
            return $this->publish($distribution, $payload);
        }

        $token = $this->accessToken($channel);
        $thumbMediaId = $this->uploadThumb($channel, $token, $article);

        $resp = Http::post(self::BASE.'/draft/update?access_token='.$token, [
            'media_id' => $mediaId,
            'index' => 0,
            'articles' => [
                'title'              => (string) ($article['title'] ?? ''),
                'author'             => (string) ($article['author'] ?? '斓姿GEO Backend'),
                'digest'             => (string) ($article['excerpt'] ?? ''),
                'content'            => (string) ($article['content_html'] ?? ''),
                'thumb_media_id'     => $thumbMediaId,
            ],
        ]);

        $result = $resp->json();
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            throw new RuntimeException('微信草稿更新失败: '.$result['errmsg']);
        }

        return [
            'remote_id' => $mediaId,
            'remote_url' => '',
            'remote_meta' => ['wechat_media_id' => $mediaId, 'status' => 'draft_updated'],
        ];
    }

    public function delete(ArticleDistribution $distribution): array
    {
        $distribution->loadMissing('channel');
        $channel = $this->channel($distribution);
        $mediaId = $distribution->remote_id;

        if (!$mediaId) {
            return ['deleted' => true, 'remote_id' => null, 'remote_url' => null];
        }

        $token = $this->accessToken($channel);
        Http::post(self::BASE.'/draft/delete?access_token='.$token, ['media_id' => $mediaId]);

        return ['deleted' => true, 'remote_id' => $mediaId, 'remote_url' => null];
    }

    public function syncSiteSettings(DistributionChannel $channel): array
    {
        return ['ok' => true, 'skipped' => true, 'reason' => 'wechat_mp_no_remote_settings'];
    }

    // ── 内部方法 ──

    private function channel(ArticleDistribution $dist): DistributionChannel
    {
        if (!$dist->channel instanceof DistributionChannel) {
            throw new RuntimeException('分发记录缺少微信渠道。');
        }
        return $dist->channel;
    }

    private function config(DistributionChannel $ch): array
    {
        return $ch->resolvedChannelConfig();
    }

    private function accessToken(DistributionChannel $channel): string
    {
        $cfg = $this->config($channel);
        $appId = $cfg['wechat_app_id'] ?? '';
        $secret = $cfg['wechat_app_secret'] ?? '';

        if (!$appId || !$secret) {
            throw new RuntimeException('微信公众号 AppID 或 AppSecret 未配置');
        }

        $resp = Http::get('https://api.weixin.qq.com/cgi-bin/token', [
            'grant_type' => 'client_credential',
            'appid' => $appId,
            'secret' => $secret,
        ]);
        $data = $resp->json();

        if (isset($data['errcode']) && $data['errcode'] !== 0) {
            throw new RuntimeException('微信access_token获取失败: '.($data['errmsg'] ?? 'unknown'));
        }

        return (string) ($data['access_token'] ?? '');
    }

    /**
     * 上传缩略图（如果有featured_image），返回 thumb_media_id
     */
    private function uploadThumb(DistributionChannel $channel, string $token, array $article): string
    {
        $imageUrl = $article['featured_image_url'] ?? '';
        if (!$imageUrl) {
            return '';
        }

        try {
            // 下载图片到临时文件
            $imgResp = Http::get($imageUrl);
            if ($imgResp->failed()) {
                return '';
            }
            $tmpPath = tempnam(sys_get_temp_dir(), 'wx_thumb_');
            file_put_contents($tmpPath, $imgResp->body());

            // 上传到微信素材
            $uploadResp = Http::attach(
                'media',
                file_get_contents($tmpPath),
                'thumb.jpg',
                ['Content-Type' => 'image/jpeg']
            )->post(self::BASE.'/material/add_material?access_token='.$token.'&type=thumb');

            @unlink($tmpPath);

            $data = $uploadResp->json();
            return (string) ($data['media_id'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
