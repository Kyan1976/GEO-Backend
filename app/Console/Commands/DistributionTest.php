<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\DistributionChannel;
use App\Models\ArticleDistribution;
use App\Services\GeoFlow\DistributionOrchestrator;
use Illuminate\Console\Command;

class DistributionTest extends Command
{
    protected $signature = 'distribution:test {--article= : 文章ID} {--channel= : 渠道ID}';

    protected $description = '分发测试：将一篇文章分发到指定渠道';

    public function handle(DistributionOrchestrator $orchestrator): int
    {
        $articleId = (int) $this->option('article');
        $channelId = (int) $this->option('channel');

        $this->info("=== GEOFlow 分发测试工具 ===");

        // 显示已发布文章列表
        if ($articleId <= 0) {
            $pubArticles = Article::where('status', 'published')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'status', 'created_at']);
            $this->line("\n已发布文章（最近10篇）：");
            $rows = [];
            foreach ($pubArticles as $a) {
                $rows[] = [$a->id, mb_substr($a->title, 0, 50), $a->status, $a->created_at->format('Y-m-d')];
            }
            $this->table(['ID', '标题', '状态', '日期'], $rows);

            $articleId = $this->ask('请输入要分发的文章ID');
            if (!$articleId) return self::FAILURE;
            $articleId = (int) $articleId;
        }

        $article = Article::find($articleId);
        if (!$article) {
            $this->error("文章 #{$articleId} 不存在");
            return self::FAILURE;
        }
        $this->info("已选择文章: [{$article->id}] {$article->title}");

        // 显示活跃渠道列表
        if ($channelId <= 0) {
            $channels = DistributionChannel::where('status', 'active')->get();
            if ($channels->isEmpty()) {
                $this->warn("\n没有活跃的渠道。请先使用 channel:activate 激活渠道。");
                $channels = DistributionChannel::all();
            }
            $rows = [];
            foreach ($channels as $c) {
                $rows[] = [$c->id, $c->name, $c->channel_type, $c->status];
            }
            $this->table(['ID', '名称', '类型', '状态'], $rows);

            $channelId = $this->ask('请输入渠道ID');
            if (!$channelId) return self::FAILURE;
            $channelId = (int) $channelId;
        }

        $channel = DistributionChannel::find($channelId);
        if (!$channel) {
            $this->error("渠道 #{$channelId} 不存在");
            return self::FAILURE;
        }
        $this->info("已选择渠道: [{$channel->id}] {$channel->name} ({$channel->channel_type})");

        if ($this->confirm('确认执行分发测试?', true)) {
            // 创建 distribution 记录
            $dist = ArticleDistribution::create([
                'article_id' => $article->id,
                'distribution_channel_id' => $channel->id,
                'action' => 'publish',
                'status' => 'queued',
                'next_retry_at' => now(),
                'idempotency_key' => 'test-' . $article->id . '-' . $channel->id . '-' . time(),
            ]);

            $orchestrator->log('info', '分发测试 - 任务已入队', $channel->id, $dist->id, $article->id, [
                'event' => 'distribution.test_queued',
            ]);

            $this->info("分发记录已创建 (ID: {$dist->id})");
            $this->line("执行分发处理...");

            try {
                $orchestrator->process($dist);
                $this->info("分发处理完成!");

                $dist->refresh();
                $this->table(
                    ['属性', '值'],
                    [
                        ['分发ID', (string) $dist->id],
                        ['状态', $dist->status],
                        ['远程ID', $dist->remote_id ?? '无'],
                        ['远程URL', $dist->remote_url ?? '无'],
                        ['错误信息', $dist->last_error_message ?? '无'],
                    ]
                );
            } catch (\Throwable $e) {
                $this->warn("分发处理异常: " . $e->getMessage());
                $dist->refresh();
                $this->line("当前状态: " . ($dist->status ?? '未知'));
                $this->line("远程ID: " . ($dist->remote_id ?? '无'));
                $this->line("错误: " . ($dist->last_error_message ?? '无'));
            }

            // 查看日志
            $logs = \App\Models\DistributionLog::where('article_distribution_id', $dist->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
            if ($logs->isNotEmpty()) {
                $this->line("\n分发日志:");
                $logRows = [];
                foreach ($logs as $log) {
                    $logRows[] = [$log->id, $log->level, mb_substr($log->message, 0, 80), $log->created_at->format('H:i:s')];
                }
                $this->table(['ID', '级别', '消息', '时间'], $logRows);
            }
        }

        return self::SUCCESS;
    }
}
