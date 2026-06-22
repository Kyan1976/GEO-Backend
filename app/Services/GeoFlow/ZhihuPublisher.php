<?php

namespace App\Services\GeoFlow;

use App\Models\ArticleDistribution;
use App\Models\DistributionChannel;

/**
 * 知乎 Publisher — 半自动（格式化+复制）
 *
 * 知乎有创作中心API但需申请，目前采用半自动：
 * - GEOFlow按知乎专栏格式生成内容（标题+正文+话题标签）
 * - 存入remote_meta，前端提供"一键复制"
 * - 用户复制后粘贴到知乎专栏/回答发布
 */
class ZhihuPublisher implements DistributionPublisherInterface
{
    public function health(DistributionChannel $channel): array
    {
        return [
            'ok' => true,
            'channel_type' => 'zhihu_copy',
            'mode' => 'semi-auto',
            'note' => '知乎无公开API，使用一键复制方式',
        ];
    }

    public function publish(ArticleDistribution $distribution, array $payload): array
    {
        $article = $payload['article'] ?? [];

        $title = $this->formatTitle($article['title'] ?? '');
        $bodyHtml = $this->formatBodyHtml($article['content_html'] ?? '');
        $bodyText = $this->stripHtml($bodyHtml);
        $topics = $this->formatTopics($article['keywords'] ?? '');

        $fullContent = $title . "\n\n" . $bodyText;
        if ($topics) {
            $fullContent .= "\n\n" . $topics;
        }

        return [
            'remote_id' => 'zhihu_' . ($distribution->article_id ?? 'unknown'),
            'remote_url' => '',
            'remote_meta' => [
                'platform' => 'zhihu',
                'mode' => 'copy_paste',
                'formatted_title' => $title,
                'formatted_body_html' => $bodyHtml,
                'formatted_body_text' => $bodyText,
                'formatted_topics' => $topics,
                'full_copy_content' => $fullContent,
                'word_count' => mb_strlen($fullContent),
                'status' => 'ready_to_copy',
                'publish_note' => '请复制内容后前往知乎专栏发布。知乎编辑器支持直接粘贴富文本。',
            ],
        ];
    }

    public function update(ArticleDistribution $distribution, array $payload): array
    {
        return $this->publish($distribution, $payload);
    }

    public function delete(ArticleDistribution $distribution): array
    {
        return ['deleted' => true, 'remote_id' => null, 'remote_url' => null];
    }

    public function syncSiteSettings(DistributionChannel $channel): array
    {
        return ['ok' => true, 'skipped' => true, 'reason' => 'zhihu_no_remote_settings'];
    }

    private function formatTitle(string $title): string
    {
        $clean = $this->stripHtml($title);
        return mb_substr($clean, 0, 100);
    }

    private function formatBodyHtml(string $html): string
    {
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/ (class|style)="[^"]*"/i', '', $html);
        return trim($html);
    }

    private function formatTopics(string $keywords): string
    {
        if (empty($keywords)) {
            return '';
        }
        $tags = array_filter(array_map('trim', preg_split('/[，,、\n]/', $keywords)));
        return implode(' ', array_map(function ($tag) {
            return '【' . $this->stripHtml($tag) . '】';
        }, $tags));
    }

    private function stripHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($text);
    }
}
