@extends('admin.layouts.app')

@php
    $channelType = old('channel_type', 'geoflow_agent');
    $frontMode = old('front_mode', 'static');
    $genericAuthType = old('generic_auth_type', 'bearer');
    $genericEndpointMethods = [
        'health' => ['GET', 'POST'],
        'publish' => ['POST', 'PUT', 'PATCH'],
        'update' => ['POST', 'PUT', 'PATCH'],
        'delete' => ['DELETE', 'POST'],
        'settings' => ['POST', 'PUT', 'PATCH'],
    ];
@endphp

@section('content')
    <div class="px-4 sm:px-0">
        <div class="mb-8 flex items-center space-x-4">
            <a href="{{ route('admin.distribution.index') }}" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('admin.distribution.create_heading') }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('admin.distribution.create_subtitle') }}</p>
            </div>
        </div>

        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-6">
                <form method="POST" action="{{ route('admin.distribution.store') }}" class="space-y-6">
                    @csrf

                    {{-- 渠道名称 --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.field.name') }} *</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="{{ __('admin.distribution.placeholder.name') }}">
                    </div>

                    {{-- 渠道类型选择（统一 grid） --}}
                    <fieldset class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <legend class="text-sm font-medium text-gray-900">{{ __('admin.distribution.field.channel_type') }}</legend>
                        <p class="mt-1 text-sm text-gray-600">{{ __('admin.distribution.help.channel_type') }}</p>
                        <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="geoflow_agent" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'geoflow_agent')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">{{ __('admin.distribution.channel_type.geoflow_agent') }}</span>
                                    <span class="mt-1 block text-sm text-gray-600">{{ __('admin.distribution.channel_type.geoflow_agent_desc') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="wordpress_rest" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'wordpress_rest')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">{{ __('admin.distribution.channel_type.wordpress_rest') }}</span>
                                    <span class="mt-1 block text-sm text-gray-600">{{ __('admin.distribution.channel_type.wordpress_rest_desc') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="generic_http_api" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'generic_http_api')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">{{ __('admin.distribution.channel_type.generic_http_api') }}</span>
                                    <span class="mt-1 block text-sm text-gray-600">{{ __('admin.distribution.channel_type.generic_http_api_desc') }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="wechat_mp" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'wechat_mp')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">微信公众号</span>
                                    <span class="mt-1 block text-sm text-gray-600">通过微信公众号API创建草稿（需认证服务号）</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="toutiao_api" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'toutiao_api')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">头条号</span>
                                    <span class="mt-1 block text-sm text-gray-600">通过头条号开放平台API发布图文（需OAuth2授权）</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="xiaohongshu_copy" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'xiaohongshu_copy')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">小红书</span>
                                    <span class="mt-1 block text-sm text-gray-600">按小红书格式生成内容（半自动，一键复制粘贴）</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="zhihu_copy" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'zhihu_copy')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">知乎</span>
                                    <span class="mt-1 block text-sm text-gray-600">按知乎专栏格式生成内容（半自动，一键复制粘贴）</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                <input type="radio" name="channel_type" value="baidu_baike_copy" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($channelType === 'baidu_baike_copy')>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900">百度百科</span>
                                    <span class="mt-1 block text-sm text-gray-600">生成百科词条草稿（半自动，一键复制后人工提交）</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    {{-- 域名和接口地址 --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="domain" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.field.domain') }} *</label>
                            <input id="domain" name="domain" type="text" required value="{{ old('domain') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="example.com">
                        </div>
                        <div>
                            <label for="endpoint_url" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.field.endpoint_url') }} *</label>
                            <input id="endpoint_url" name="endpoint_url" type="text" required value="{{ old('endpoint_url') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ __('admin.distribution.placeholder.endpoint_url') }}">
                            <p class="mt-1 text-xs text-gray-500">{{ __('admin.distribution.help.endpoint_url') }}</p>
                        </div>
                    </div>

                    {{-- WordPress REST API 配置 --}}
                    <div data-channel-type-panel="wordpress_rest" @class(['rounded-lg border border-blue-100 bg-blue-50 p-5', 'hidden' => $channelType !== 'wordpress_rest'])>
                        <div class="mb-5">
                            <h2 class="text-lg font-medium text-gray-900">{{ __('admin.distribution.wordpress.section_title') }}</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">{{ __('admin.distribution.wordpress.section_desc') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="wordpress_username" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.username') }}</label>
                                <input id="wordpress_username" name="wordpress_username" type="text" value="{{ old('wordpress_username') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="editor">
                            </div>
                            <div>
                                <label for="wordpress_application_password" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.application_password') }}</label>
                                <input id="wordpress_application_password" name="wordpress_application_password" type="password" value="{{ old('wordpress_application_password') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    autocomplete="new-password">
                                <p class="mt-1 text-xs text-gray-500">{{ __('admin.distribution.wordpress.application_password_help') }}</p>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="wordpress_post_status" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.post_status') }}</label>
                                <select id="wordpress_post_status" name="wordpress_post_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach (['publish', 'draft', 'pending', 'private'] as $status)
                                        <option value="{{ $status }}" @selected(old('wordpress_post_status', 'draft') === $status)>{{ __('admin.distribution.wordpress.post_status_'.$status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="wordpress_image_strategy" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.image_strategy') }}</label>
                                <select id="wordpress_image_strategy" name="wordpress_image_strategy"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="upload_to_media" @selected(old('wordpress_image_strategy', 'upload_to_media') === 'upload_to_media')>{{ __('admin.distribution.wordpress.image_upload_to_media') }}</option>
                                    <option value="keep_original" @selected(old('wordpress_image_strategy') === 'keep_original')>{{ __('admin.distribution.wordpress.image_keep_original') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="wordpress_category_strategy" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.category_strategy') }}</label>
                                <select id="wordpress_category_strategy" name="wordpress_category_strategy"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="match_or_create" @selected(old('wordpress_category_strategy', 'match_or_create') === 'match_or_create')>{{ __('admin.distribution.wordpress.category_match_or_create') }}</option>
                                    <option value="match_only" @selected(old('wordpress_category_strategy') === 'match_only')>{{ __('admin.distribution.wordpress.category_match_only') }}</option>
                                    <option value="fixed" @selected(old('wordpress_category_strategy') === 'fixed')>{{ __('admin.distribution.wordpress.category_fixed') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="wordpress_fixed_category" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.fixed_category') }}</label>
                                <input id="wordpress_fixed_category" name="wordpress_fixed_category" type="text" value="{{ old('wordpress_fixed_category') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="1 或 News">
                            </div>
                            <div>
                                <label for="wordpress_tag_strategy" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.wordpress.tag_strategy') }}</label>
                                <select id="wordpress_tag_strategy" name="wordpress_tag_strategy"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="keywords_to_tags" @selected(old('wordpress_tag_strategy', 'keywords_to_tags') === 'keywords_to_tags')>{{ __('admin.distribution.wordpress.tag_keywords_to_tags') }}</option>
                                    <option value="disabled" @selected(old('wordpress_tag_strategy') === 'disabled')>{{ __('admin.distribution.wordpress.tag_disabled') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 通用 HTTP API 配置 --}}
                    <div data-channel-type-panel="generic_http_api" @class(['rounded-lg border border-indigo-100 bg-indigo-50 p-5', 'hidden' => $channelType !== 'generic_http_api'])>
                        <div class="mb-5">
                            <h2 class="text-lg font-medium text-gray-900">{{ __('admin.distribution.generic.section_title') }}</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">{{ __('admin.distribution.generic.section_desc') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="generic_auth_type" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.auth_type') }}</label>
                                <select id="generic_auth_type" name="generic_auth_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach (['bearer', 'none', 'basic', 'header_key', 'hmac'] as $authType)
                                        <option value="{{ $authType }}" @selected($genericAuthType === $authType)>{{ __('admin.distribution.generic.auth_'.$authType) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div data-generic-auth-row="basic">
                                <label for="generic_basic_username" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.basic_username') }}</label>
                                <input id="generic_basic_username" name="generic_basic_username" type="text" value="{{ old('generic_basic_username') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="api-user">
                            </div>
                            <div data-generic-auth-secret>
                                <label for="generic_secret" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.secret') }}</label>
                                <input id="generic_secret" name="generic_secret" type="password" value="{{ old('generic_secret') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    autocomplete="new-password">
                                <p class="mt-1 text-xs text-gray-500">{{ __('admin.distribution.generic.secret_help') }}</p>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div data-generic-auth-row="header_key">
                                <label for="generic_header_name" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.header_name') }}</label>
                                <input id="generic_header_name" name="generic_header_name" type="text" value="{{ old('generic_header_name', 'X-API-Key') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="generic_timeout_seconds" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.timeout_seconds') }}</label>
                                <input id="generic_timeout_seconds" name="generic_timeout_seconds" type="number" min="5" max="120" value="{{ old('generic_timeout_seconds', 30) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="generic_success_statuses" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.success_statuses') }}</label>
                                <input id="generic_success_statuses" name="generic_success_statuses" type="text" value="{{ old('generic_success_statuses', '200,201,202,204') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-6 rounded-lg border border-indigo-100 bg-white p-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('admin.distribution.generic.endpoint_section') }}</h3>
                            <p class="mt-1 text-xs leading-5 text-gray-500">{{ __('admin.distribution.generic.endpoint_help') }}</p>
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach ([
                                    ['generic_health_method', 'generic_health_path', 'health', 'GET', '/health'],
                                    ['generic_publish_method', 'generic_publish_path', 'publish', 'POST', '/articles'],
                                    ['generic_update_method', 'generic_update_path', 'update', 'POST', '/articles/{remote_id}'],
                                    ['generic_delete_method', 'generic_delete_path', 'delete', 'DELETE', '/articles/{remote_id}'],
                                    ['generic_settings_method', 'generic_settings_path', 'settings', 'POST', ''],
                                ] as [$methodName, $pathName, $labelKey, $defaultMethod, $defaultPath])
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label for="{{ $methodName }}" class="block text-xs font-medium text-gray-600">{{ __('admin.distribution.generic.endpoint_'.$labelKey) }}</label>
                                            <select id="{{ $methodName }}" name="{{ $methodName }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach ($genericEndpointMethods[$labelKey] as $method)
                                                    <option value="{{ $method }}" @selected(old($methodName, $defaultMethod) === $method)>{{ $method }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-2">
                                            <label for="{{ $pathName }}" class="block text-xs font-medium text-gray-600">{{ __('admin.distribution.generic.path') }}</label>
                                            <input id="{{ $pathName }}" name="{{ $pathName }}" type="text" value="{{ old($pathName, $defaultPath) }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="generic_payload_wrapper" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.payload_wrapper') }}</label>
                                <select id="generic_payload_wrapper" name="generic_payload_wrapper"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="none" @selected(old('generic_payload_wrapper', 'none') === 'none')>{{ __('admin.distribution.generic.wrapper_none') }}</option>
                                    <option value="data" @selected(old('generic_payload_wrapper') === 'data')>{{ __('admin.distribution.generic.wrapper_data') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="generic_remote_id_path" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.remote_id_path') }}</label>
                                <input id="generic_remote_id_path" name="generic_remote_id_path" type="text" value="{{ old('generic_remote_id_path', 'id') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="data.id">
                            </div>
                            <div>
                                <label for="generic_remote_url_path" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.generic.remote_url_path') }}</label>
                                <input id="generic_remote_url_path" name="generic_remote_url_path" type="text" value="{{ old('generic_remote_url_path', 'url') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="data.url">
                            </div>
                        </div>
                    </div>

                    {{-- 微信公众号配置 --}}
                    <div data-channel-type-panel="wechat_mp" @class(['rounded-lg border border-green-100 bg-green-50 p-5 space-y-4', 'hidden' => $channelType !== 'wechat_mp'])>
                        <h3 class="text-sm font-semibold text-green-900">微信公众号配置</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">AppID</label>
                            <input type="text" name="channel_config[wechat_app_id]" value="{{ old('channel_config.wechat_app_id', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="wx1234567890abcdef">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">AppSecret</label>
                            <input type="password" name="channel_config[wechat_app_secret]" value="{{ old('channel_config.wechat_app_secret', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="AppSecret">
                        </div>
                        <div class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                            <strong>注意：</strong>发布后文章进入公众号草稿箱，需手动群发。
                        </div>
                    </div>

                    {{-- 头条号配置 --}}
                    <div data-channel-type-panel="toutiao_api" @class(['rounded-lg border border-orange-100 bg-orange-50 p-5 space-y-4', 'hidden' => $channelType !== 'toutiao_api'])>
                        <h3 class="text-sm font-semibold text-orange-900">头条号配置</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Access Token</label>
                            <input type="text" name="channel_config[toutiao_access_token]" value="{{ old('channel_config.toutiao_access_token', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="OAuth2授权后的access_token">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Open ID</label>
                            <input type="text" name="channel_config[toutiao_open_id]" value="{{ old('channel_config.toutiao_open_id', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="授权用户的open_id">
                        </div>
                        <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">
                            <strong>如何获取：</strong>前往 <a href="https://open.douyin.com" target="_blank" class="underline">抖音开放平台</a> 注册应用并完成OAuth2授权。
                        </div>
                    </div>

                    {{-- 小红书配置 --}}
                    <div data-channel-type-panel="xiaohongshu_copy" @class(['rounded-lg border border-red-100 bg-red-50 p-5 space-y-4', 'hidden' => $channelType !== 'xiaohongshu_copy'])>
                        <h3 class="text-sm font-semibold text-red-900">小红书内容配置</h3>
                        <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">
                            <strong>半自动模式：</strong>GEOFlow按小红书格式生成（标题≤20字、正文≤1000字、#标签），文章详情页提供"一键复制"，粘贴到小红书App发布。
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">默认标签（可选）</label>
                            <input type="text" name="channel_config[xhs_default_tags]" value="{{ old('channel_config.xhs_default_tags', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="女装、穿搭、面料">
                        </div>
                    </div>

                    {{-- 知乎配置 --}}
                    <div data-channel-type-panel="zhihu_copy" @class(['rounded-lg border border-blue-100 bg-blue-50 p-5 space-y-4', 'hidden' => $channelType !== 'zhihu_copy'])>
                        <h3 class="text-sm font-semibold text-blue-900">知乎专栏配置</h3>
                        <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">
                            <strong>半自动模式：</strong>GEOFlow按知乎专栏格式生成（标题+富文本+话题标签），文章详情页提供"一键复制"，粘贴到知乎专栏发布。
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">默认话题（可选）</label>
                            <input type="text" name="channel_config[zhihu_default_topics]" value="{{ old('channel_config.zhihu_default_topics', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="服装面料、时尚穿搭、女装">
                        </div>
                    </div>

                    {{-- 百度百科配置 --}}
                    <div data-channel-type-panel="baidu_baike_copy" @class(['rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-4', 'hidden' => $channelType !== 'baidu_baike_copy'])>
                        <h3 class="text-sm font-semibold text-gray-900">百度百科词条配置</h3>
                        <div class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                            <strong>半自动模式：</strong>GEOFlow将文章转为百科词条格式（词条名+摘要+分段正文+参考资料），建议人工审改后提交。
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">参考资料来源（可选）</label>
                            <input type="text" name="channel_config[baike_reference_url]" value="{{ old('channel_config.baike_reference_url', '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="https://www.lanzi.com/about">
                        </div>
                    </div>

                    {{-- GEOFlow Agent 配置 --}}
                    <div data-channel-type-panel="geoflow_agent" @class(['space-y-6', 'hidden' => $channelType !== 'geoflow_agent'])>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="template_key" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.field.template_key') }}</label>
                                <input id="template_key" name="template_key" type="text" value="{{ old('template_key') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="default">
                            </div>
                        </div>
                        <fieldset class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <legend class="text-sm font-medium text-gray-900">{{ __('admin.distribution.field.front_mode') }}</legend>
                            <p class="mt-1 text-sm text-gray-600">{{ __('admin.distribution.help.front_mode') }}</p>
                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                    <input type="radio" name="front_mode" value="static" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($frontMode === 'static')>
                                    <span>
                                        <span class="block text-sm font-semibold text-gray-900">{{ __('admin.distribution.front_mode.static') }}</span>
                                        <span class="mt-1 block text-sm text-gray-600">{{ __('admin.distribution.front_mode.static_desc') }}</span>
                                    </span>
                                </label>
                                <label class="flex cursor-pointer gap-3 rounded-md border border-gray-200 bg-white p-4 hover:border-blue-300">
                                    <input type="radio" name="front_mode" value="rewrite" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($frontMode === 'rewrite')>
                                    <span>
                                        <span class="block text-sm font-semibold text-gray-900">{{ __('admin.distribution.front_mode.rewrite') }}</span>
                                        <span class="mt-1 block text-sm text-gray-600">{{ __('admin.distribution.front_mode.rewrite_desc') }}</span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    {{-- 状态和描述 --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('admin.distribution.field.status') }}</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="active" @selected(old('status', 'active') === 'active')>{{ __('admin.distribution.status.active') }}</option>
                                <option value="paused" @selected(old('status') === 'paused')>{{ __('admin.distribution.status.paused') }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">{{ __('admin.common.description') }}</label>
                        <textarea id="description" name="description" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="{{ __('admin.distribution.placeholder.description') }}">{{ old('description') }}</textarea>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.distribution.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('admin.button.cancel') }}
                        </a>
                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <i data-lucide="key-round" class="mr-2 h-4 w-4"></i>
                            {{ __('admin.distribution.button.save_and_generate_secret') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('change', function (event) {
            if (event.target.matches('[name="channel_type"]')) {
                document.querySelectorAll('[data-channel-type-panel]').forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.channelTypePanel !== event.target.value);
                });
            }
            if (event.target.matches('#generic_auth_type')) {
                toggleGenericAuthFields();
            }
        });

        function toggleGenericAuthFields() {
            var select = document.getElementById('generic_auth_type');
            if (!select) return;
            var authType = select.value;
            document.querySelectorAll('[data-generic-auth-row]').forEach(function (field) {
                field.classList.toggle('hidden', field.dataset.genericAuthRow !== authType);
            });
            document.querySelectorAll('[data-generic-auth-secret]').forEach(function (field) {
                field.classList.toggle('hidden', authType === 'none');
            });
        }
        toggleGenericAuthFields();
    </script>
@endsection
