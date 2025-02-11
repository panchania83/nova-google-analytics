<?php

namespace Panchania83\NovaGoogleAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\Period;

class ReferrerListController extends Controller
{
    public function __invoke(Request $request): Collection
    {
        return $this->topReferrers($request);
    }

    private function topReferrers(Request $request): Collection
    {
        $duration = $request->has('duration')
            ? $request->input('duration')
            : 'week';

        switch($duration) {
            case 'week':
                $period = Period::days(7);
                break;
            case 'month':
                $period = Period::months(1);
                break;
            case 'year':
                $period = Period::years(1);
                break;
            default:
                $period = Period::days(7);
                break;
        }

        $analyticsData = app(Analytics::class)
            ->performQuery(
                $period,
                'ga:users',
                [
                    'dimensions' => 'ga:fullReferrer',
                    'sort' => '-ga:users',
                    'max-results' => 10,
                ]
            );

        return collect($analyticsData['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'url' => $pageRow[0],
                'pageViews' => (int) $pageRow[1],
            ];
        });
    }
}
