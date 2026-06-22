<?php

namespace App\Console\Commands;

use App\Models\DistributionChannel;
use App\Services\GeoFlow\DistributionOrchestrator;
use Illuminate\Console\Command;

class ChannelHealthCheck extends Command
{
    protected $signature = 'channel:health-check';

    protected $description = '检查所有分发渠道的健康状态';

    public function handle(DistributionOrchestrator $orchestrator): int
    {
        $channels = DistributionChannel::query()->orderBy('id')->get();

        if ($channels->isEmpty()) {
            $this->warn('未找到任何分发渠道。');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($channels as $channel) {
            if ($channel->status !== 'active') {
                $rows[] = [
                    (string) $channel->id,
                    $channel->name,
                    $channel->channel_type,
                    $channel->status,
                    "\xe2\x80\x94",
                    '未激活',
                ];
                continue;
            }

            try {
                $result = $orchestrator->healthCheck($channel);
                $healthy = ($result['healthy'] ?? false) ? "\xe2\x9c\x85 \xe6\xad\xa3\xe5\xb8\xb8" : "\xe2\x9d\x8c \xe5\xbc\x82\xe5\xb8\xb8";
                $error = $result['error'] ?? ($result['message'] ?? '');
            } catch (\Throwable $e) {
                $healthy = "\xe2\x9d\x8c \xe5\xbc\x82\xe5\xb8\xb8";
                $error = $e->getMessage();
            }

            $rows[] = [
                (string) $channel->id,
                $channel->name,
                $channel->channel_type,
                $channel->status,
                $healthy,
                mb_substr((string) $error, 0, 200),
            ];
        }

        $this->table(
            ['ID', "\xe5\x90\x8d\xe7\xa7\xb0", "\xe7\xb1\xbb\xe5\x9e\x8b", "\xe7\x8a\xb6\xe6\x80\x81", "\xe5\x81\xa5\xe5\xba\xb7\xe7\x8a\xb6\xe6\x80\x81", "\xe9\x94\x99\xe8\xaf\xaf\xe4\xbf\xa1\xe6\x81\xaf"],
            $rows
        );

        return self::SUCCESS;
    }
}
