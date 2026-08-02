<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\Post;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $days = (int) $request->input('days', 30);
        $days = in_array($days, [1, 7, 30, 90], true) ? $days : 30;
        $includeBots = $request->boolean('bots');

        $base = fn () => PageView::query()
            ->since($days)
            ->when(! $includeBots, fn ($q) => $q->humans());

        return Inertia::render('Admin/Analytics/Index', [
            'filters' => ['days' => $days, 'bots' => $includeBots],
            'summary' => [
                'totalViews' => (clone $base())->count(),
                'botViews' => PageView::query()->since($days)->where('is_bot', true)->count(),
                'uniquePaths' => (clone $base())->distinct('path')->count('path'),
                'countriesSeen' => (clone $base())->whereNotNull('country_code')->distinct('country_code')->count('country_code'),
            ],
            'topPages' => $this->topPages($base(), $days),
            'byType' => (clone $base())
                ->select('subject_type', DB::raw('count(*) as views'))
                ->groupBy('subject_type')
                ->orderByDesc('views')
                ->get(),
            'byCountry' => (clone $base())
                ->select('country_code', DB::raw('count(*) as views'))
                ->groupBy('country_code')
                ->orderByDesc('views')
                ->limit(20)
                ->get()
                ->map(fn ($row) => [
                    'country_code' => $row->country_code ?? 'Unknown',
                    'views' => $row->views,
                ]),
            'byReferrer' => (clone $base())
                ->whereNotNull('referrer_host')
                ->select('referrer_host', DB::raw('count(*) as views'))
                ->groupBy('referrer_host')
                ->orderByDesc('views')
                ->limit(15)
                ->get(),
            'daily' => (clone $base())
                ->select(DB::raw('date(viewed_at) as day'), DB::raw('count(*) as views'))
                ->groupBy('day')
                ->orderBy('day')
                ->get(),
            'geoAvailable' => PageView::whereNotNull('country_code')->exists(),
        ]);
    }

    /**
     * Top paths, with the human-readable title resolved from the slug so the
     * table shows "Loan / EMI Calculator" rather than just "/tools/loan-calculator/".
     * Resolved here in one batch rather than stored per row, so the hot write
     * path in RecordPageView stays a single insert with no lookups.
     */
    protected function topPages($query, int $days): array
    {
        $rows = (clone $query)
            ->select('path', 'subject_type', DB::raw('count(*) as views'))
            ->groupBy('path', 'subject_type')
            ->orderByDesc('views')
            ->limit(50)
            ->get();

        $toolSlugs = [];
        $postSlugs = [];

        foreach ($rows as $row) {
            $slug = $this->slugFrom($row->path);
            if (! $slug) {
                continue;
            }
            if ($row->subject_type === 'tool') {
                $toolSlugs[] = $slug;
            } elseif (in_array($row->subject_type, ['blog', 'news'], true)) {
                $postSlugs[] = $slug;
            }
        }

        $tools = Tool::whereIn('slug', $toolSlugs)->pluck('title', 'slug');
        $posts = Post::whereIn('slug', $postSlugs)->get(['slug', 'title', 'type'])
            ->keyBy(fn ($p) => $p->type.':'.$p->slug);

        return $rows->map(function ($row) use ($tools, $posts) {
            $slug = $this->slugFrom($row->path);

            $title = match (true) {
                $row->subject_type === 'tool' => $tools[$slug] ?? null,
                in_array($row->subject_type, ['blog', 'news'], true) => $posts[$row->subject_type.':'.$slug]->title ?? null,
                $row->subject_type === 'home' => 'Homepage',
                $row->subject_type === 'tools-index' => 'All tools (index)',
                $row->subject_type === 'blog-index' => 'Blog (index)',
                $row->subject_type === 'news-index' => 'News (index)',
                default => null,
            };

            return [
                'path' => $row->path,
                'subject_type' => $row->subject_type,
                'title' => $title,
                'views' => $row->views,
            ];
        })->all();
    }

    protected function slugFrom(string $path): ?string
    {
        $segments = array_values(array_filter(explode('/', $path)));

        return count($segments) >= 2 ? end($segments) : null;
    }
}
