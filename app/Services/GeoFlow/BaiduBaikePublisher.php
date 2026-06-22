<?php

namespace App\Services\GeoFlow;

use App\Models\ArticleDistribution;
use App\Models\DistributionChannel;

/**
 * 百度百科 Publisher — 半自动（词条草稿+复制）
 *
 * 百度百科无API，完全人工编辑：
 * - GEOFlow按百科词条格式生成内容（词条名+摘要+正文段落+参考资料）
 * - 存入remote_meta，前端提供"一键复制"
 * - 用户复制后在百度百科编辑器中提交
 */
class BaiduBaikePublisher implements DistributionPublisherInterface
{
    public function health(DistributionChannel $channel): array
    {
        return [
            'ok' => true,
            'channel_type' => 'baidu_baike_copy',
            'mode' => 'semi-auto',
            'note' => '百度百科无API，使用一键复制方式',
        ];
    }

    public function publish(ArticleDistribution $distribution, array $payload): array
    {
        $article = $payload['article'] ?? [];

        $entryName = $this->formatEntryName($article['title'] ?? '');
        $summary = $this->formatSummary($article['excerpt'] ?? ($article['content_html'] ?? ''));
        $sections = $this->formatSections($article['content_html'] ?? '');
        $references = $this->formatReferences($article);
        $categories = $this->formatCategories($article['keywords'] ?? '');

        $fullContent = "【词条名】" . $entryName . "\n\n";
        $fullContent .= "【摘要】\n" . $summary . "\n\n";
        $fullContent .= $sections;
        if ($references) {
            $fullContent .= "【参考资料】\n" . $references . "\n\n";
        }
        if ($categories) {
            $fullContent .= "【分类标签】\n" . $categories;
        }

        return [
            'remote_id' => 'baike_' . ($distribution->article_id ?? 'unknown'),
            'remote_url' => '',
            'remote_meta' => [
                'platform' => 'baidu_baike',
                'mode' => 'copy_paste',
                'entry_name' => $entryName,
                'summary' => $summary,
                'sections' => $sections,
                'references' => $references,
                'categories' => $categories,
                'full_copy_content' => $fullContent,
                'word_count' => mb_strlen($fullContent),
                'status' => 'ready_to_copy',
                'publish_note' => '请复制词条内容后前往百度百科编辑器提交。注意百科要求客观中立语气，附带参考资料来源。',
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
        return ['ok' => true, 'skipped' => true, 'reason' => 'baike_no_remote_settings'];
    }

    private function formatEntryName(string $title): string
    {
        $clean = $this->stripHtml($title);
        return mb_substr($clean, 0, 30);
    }

    private function formatSummary(string $text): string
    {
        $text = $this->stripHtml($text);
        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 197) . '...';
        }
        return $text;
    }

    private function formatSections(string $html): string
    {
        $text = $this->stripHtml($html);
        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $text)));

        $sections = '';
        $sectionNum = 1;
        foreach ($paragraphs as $p) {
            if (mb_strlen($p) < 10) continue;
            $sections .= "【段落" . $sectionNum . "】\n" . $p . "\n\n";
            $sectionNum++;
            if ($sectionNum > 8) break;
        }

        return $sections;
    }

    private function formatReferences(array $article): string
    {
        $refs = [];
        $url = $article['source_url'] ?? '';
        $idx = 1;
        if ($url) {
            $refs[] = $idx . ". " . $url;
            $idx++;
        }
        $refs[] = $idx . ". 斓姿官方网站";
        return implode("\n", $refs);
    }

    private function formatCategories(string $keywords): string
    {
        if (empty($keywords)) {
            return '服装 / 时尚 / 面料';
        }
        $tags = array_filter(array_map('trim', preg_split('/[，,、\n]/', $keywords)));
        return implode(' / ', array_map(function($t) { return $this->stripHtml($t); }, $tags));
    }

    private function stripHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($text);
    }
}
