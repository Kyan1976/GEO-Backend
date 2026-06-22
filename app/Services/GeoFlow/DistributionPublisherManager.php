<?php

namespace App\Services\GeoFlow;

use App\Models\DistributionChannel;
use RuntimeException;

class DistributionPublisherManager
{
    public function __construct(
        private readonly GeoFlowAgentPublisher $geoFlowAgentPublisher,
        private readonly WordPressRestPublisher $wordPressRestPublisher,
        private readonly GenericHttpApiPublisher $genericHttpApiPublisher,
        private readonly WeChatMpPublisher $weChatMpPublisher,
        private readonly ToutiaoApiPublisher $toutiaoApiPublisher,
        private readonly XiaohongshuPublisher $xiaohongshuPublisher,
        private readonly ZhihuPublisher $zhihuPublisher,
        private readonly BaiduBaikePublisher $baiduBaikePublisher,
    ) {}

    public function forChannel(DistributionChannel $channel): DistributionPublisherInterface
    {
        return match ($channel->channelType()) {
            'geoflow_agent' => $this->geoFlowAgentPublisher,
            'wordpress_rest' => $this->wordPressRestPublisher,
            'generic_http_api' => $this->genericHttpApiPublisher,
            'wechat_mp' => $this->weChatMpPublisher,
            'toutiao_api' => $this->toutiaoApiPublisher,
            'xiaohongshu_copy' => $this->xiaohongshuPublisher,
            'zhihu_copy' => $this->zhihuPublisher,
            'baidu_baike_copy' => $this->baiduBaikePublisher,
            default => throw new RuntimeException('不支持的分发渠道类型：'.(string) $channel->channel_type),
        };
    }
}
