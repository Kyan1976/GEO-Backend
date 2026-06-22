<?php

namespace App\Services\GeoFlow;

use App\Models\ArticleDistribution;
use App\Models\DistributionChannel;

/**
 * 小红书 Publisher — 半自动（格式化+复制）
 *
 * 小红书无公开发布API，采用半自动策略：
 * - GEOFlow按小红书格式生成内容（标题+正文+标签）
 * - 存入remote_meta，前端提供"一键复制"按钮
 * - 用户复制后粘贴到小红书App/网页版发布
 */
class XiaohongshuPublisher implements DistributionPublisherInterface
{
    public function health(DistributionChannel $channel): array
    {
        return [
            'ok' => true,
            'channel_type' => 'xiaohongshu_copy',
            'mode' => 'semi-auto',
            'note' => '小红书无API，使用一键复制方式',
        ];
    }

    public function publish(ArticleDistribution $distribution, array $payload): array
    {
        $article = $payload['article'] ?? [];

        $title = $this->formatTitle($article['title'] ?? '');
        $body = $this->formatBody($article['content_html'] ?? ($article['content_text'] ?? ''));
        $tags = $this->formatTags($article['keywords'] ?? '');

        $fullContent = $title . "\n\n" . $body;
        if ($tags) {
            $fullContent .= "\n\n" . $tags;
        }

        return [
            'remote_id' => 'xhs_' . ($distribution->article_id ?? 'unknown'),
            'remote_url' => '',
            'remote_meta' => [
                'platform' => 'xiaohongshu',
                'mode' => 'copy_paste',
                'formatted_title' => $title,
                'formatted_body' => $body,
                'formatted_tags' => $tags,
                'full_copy_content' => $fullContent,
                'word_count' => mb_strlen($fullContent),
                'status' => 'ready_to_copy',
                'publish_note' => '请复制内容后在小红书App或网页版手动发布。标题限20字，正文限1000字。',
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
        return ['ok' => true, 'skipped' => true, 'reason' => 'xiaohongshu_no_remote_settings'];
    }

    private function formatTitle(string $title): string
    {
        $clean = $this->stripHtml($title);
        return mb_substr($clean, 0, 20);
    }

    private function formatBody(string $html): string
    {
        $text = $this->stripHtml($html);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        if (mb_strlen($text) > 1000) {
            $text = mb_substr($text, 0, 997) . '...';
        }
        return trim($text);
    }

    private function formatTags(string $keywords): string
    {
        if (empty($keywords)) {
            return '';
        }
        $tags = array_filter(array_map('trim', preg_split('/[，,、\n]/', $keywords)));
        return implode(' ', array_map(function ($tag) {
            $tag = $this->stripHtml($tag);
            $tag = trim($tag);
            if (empty($tag)) return '';
            $tag = preg_replace('/\s+/', '', $tag);
            return '#' . $tag;
        }, $tags));
    }

    private function stripHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($text);
    }
}
