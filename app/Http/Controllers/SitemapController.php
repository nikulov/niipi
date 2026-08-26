<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class SitemapController extends Controller
{
    /** @var array<class-string, string> Модель => именованный роут c параметром {slug} */
    private const array ENTITIES = [
        Post::class => 'news.show',
        Project::class => 'projects.show',
    ];

    public function __invoke(): Response
    {
        $xml = Cache::tags(['sitemap'])->remember(
            'sitemap.xml',
            now()->addHour(),
            fn (): string => view('sitemap', ['urls' => $this->urls()])->render(),
        );

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return list<array{loc: string, lastmod: ?string}>
     */
    private function urls(): array
    {
        $urls = [];

        foreach (Page::query()->published()->get(['slug', 'updated_at']) as $page) {
            $urls[] = [
                'loc' => $page->slug === 'home'
                    ? route('home')
                    : route('page.index', $page->slug),
                'lastmod' => $page->updated_at?->toAtomString(),
            ];
        }

        foreach (self::ENTITIES as $model => $route) {
            foreach ($model::query()->published()->get(['slug', 'updated_at']) as $item) {
                $urls[] = [
                    'loc' => route($route, $item->slug),
                    'lastmod' => $item->updated_at?->toAtomString(),
                ];
            }
        }

        return $urls;
    }
}
