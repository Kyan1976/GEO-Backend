<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Broadcast;

/*
 * 修复 (2026-06-04): 防止 REVERB_APP_KEY 为 null 导致 Pusher 构造函数崩溃
 * 当环境变量未正确加载时跳过频道注册
 */
if (env('REVERB_APP_KEY')) {
    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('admin.tasks', function (Admin $admin): bool {
        return (string) ($admin->status ?? '') === 'active';
    }, ['guards' => ['admin']]);
}
