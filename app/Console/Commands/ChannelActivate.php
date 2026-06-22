<?php

namespace App\Console\Commands;

use App\Models\DistributionChannel;
use App\Services\GeoFlow\DistributionOrchestrator;
use Illuminate\Console\Command;

class ChannelActivate extends Command
{
    protected $signature = 'channel:activate {--id= : 渠道ID}';

    protected $description = '激活分发渠道并执行健康检查';

    public function handle(DistributionOrchestrator $orchestrator): int
    {
        $channelId = (int) $this->option('id');
        if ($channelId <= 0) {
            $this->error('请指定渠道ID: php artisan channel:activate --id=1');
            return self::FAILURE;
        }

        $channel = DistributionChannel::query()->whereKey($channelId)->first();
        if (!$channel) {
            $this->error('渠道 #' . $channelId . ' 不存在。');
            return self::FAILURE;
        }

        $this->line('渠道: ' . $channel->name . ' (ID: ' . $channel->id . ')');
        $this->line('类型: ' . $channel->channel_type);
        $this->line('当前状态: ' . $channel->status);

        $config = $channel->channel_config ?? [];
        $missing = [];

        switch ($channel->channel_type) {
            case 'wechat_mp':
                if (empty($config['wechat_app_id'])) $missing[] = 'wechat_app_id';
                if (empty($config['wechat_app_secret'])) $missing[] = 'wechat_app_secret';
                break;
            case 'wordpress_rest':
                if (empty($config['wordpress_username'])) $missing[] = 'wordpress_username';
                if (!$channel->activeSecret) $missing[] = 'wordpress_application_password (secret)';
                break;
            case 'generic_http_api':
                $authType = $config['generic_auth_type'] ?? 'bearer';
                if ($authType !== 'none' && !$channel->activeSecret) $missing[] = 'generic_secret (secret)';
                break;
        }

        if (!empty($missing)) {
            $this->warn('配置缺失: ' . implode(', ', $missing));
            if (!$this->confirm('配置不完整，仍要继续激活吗？', false)) {
                $this->info('已取消。');
                return self::FAILURE;
            }
        }

        $channel->forceFill(['status' => 'active'])->save();
        $this->info('状态已更新为: active');

        $this->line('执行健康检查...');
        try {
            $result = $orchestrator->healthCheck($channel);
            $healthy = ($result['healthy'] ?? false);
            $message = $result['error'] ?? ($result['message'] ?? '无返回信息');

            if ($healthy) {
                $this->info("\xe2\x9c\x85 健康检查通过: " . $message);
            } else {
                $this->warn("\xe2\x9a\xa0\xef\xb8\x8f 健康检查异常: " . $message);
            }

            $this->table(
                ["\xe5\xb1\x9e\xe6\x80\xa7", "\xe5\x80\xbc"],
                [
                    ['ID', (string) $channel->id],
                    ["\xe5\x90\x8d\xe7\xa7\xb0", $channel->name],
                    ["\xe7\xb1\xbb\xe5\x9e\x8b", $channel->channel_type],
                    ["\xe7\x8a\xb6\xe6\x80\x81", $channel->status],
                    ["\xe5\x81\xa5\xe5\xba\xb7\xe7\x8a\xb6\xe6\x80\x81", $healthy ? "\xe2\x9c\x85 \xe6\xad\xa3\xe5\xb8\xb8" : "\xe2\x9d\x8c \xe5\xbc\x82\xe5\xb8\xb8"],
                    ["\xe6\xb6\x88\xe6\x81\xaf", $message],
                ]
            );
        } catch (\Throwable $e) {
            $this->error("健康检查失败: " . $e->getMessage());
        }

        return self::SUCCESS;
    }
}
