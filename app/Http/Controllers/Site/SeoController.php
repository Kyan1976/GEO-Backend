<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Support\Site\SiteSettingsBag;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    private function baseUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }

    public function robots(): Response
    {
        $baseUrl = $this->baseUrl();
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /geo_admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";
        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $baseUrl = $this->baseUrl();
        $urls = [];

        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'];

        foreach (Category::all() as $cat) {
            $urls[] = [
                'loc' => "{$baseUrl}/category/{$cat->slug}",
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        foreach (Article::query()->published()->orderByDesc('published_at')->limit(50000)->get() as $article) {
            $urls[] = [
                'loc' => "{$baseUrl}/article/{$article->slug}",
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $article->updated_at?->format('Y-m-d'),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$u['loc']}</loc>\n";
            if (!empty($u['lastmod'])) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function llmsTxt(): Response
    {
        $baseUrl = $this->baseUrl();
        $map = SiteSettingsBag::all();
        $siteName = (string) ($map['site_name'] ?? config('app.name'));
        $siteDesc = (string) ($map['site_description'] ?? '');

        $content = "# {$siteName}\n";
        $content .= "> {$siteDesc}\n\n";
        $content .= "## URL\n{$baseUrl}\n\n";
        $content .= "## 内容分类\n";
        foreach (Category::all() as $cat) {
            $content .= "- {$baseUrl}/category/{$cat->slug} - {$cat->name}\n";
        }
        $content .= "\n## 最新文章\n";
        foreach (Article::query()->published()->orderByDesc('published_at')->limit(20)->get() as $article) {
            $content .= "- {$baseUrl}/article/{$article->slug} - {$article->title}\n";
        }

        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
