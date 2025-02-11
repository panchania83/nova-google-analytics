<?php

namespace Panchania83\NovaGoogleAnalytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Spatie\Analytics\Period;

class SessionDurationMetric extends Value
{
    use MetricDiffTrait;

    public function name(): string
    {
        return __('Avg. Session Duration');
    }

    public function calculate(Request $request): ValueResult
    {
        $lookups = [
            1 => $this->sessionDurationToday(),
            'Y' => $this->sessionDurationYesterday(),
            'LW' =>$this->sessionDurationLastWeek(),
            'LM' => $this->sessionDurationLastMonth(),
            7 => $this->sessionDurationLastSevenDays(),
            30 => $this->sessionDurationLastThirtyDays(),
        ];

        $data = Arr::get($lookups, $request->get('range'), ['result' => 0, 'previous' => 0]);

        return (new ValueResult($data['result']))->previous($data['previous'])->format('00:00:00');
    }

    private function sessionDurationToday(): array
    {
        $results = $this->performQuery('ga:avgSessionDuration', 'ga:date', Period::days(1));

        return [
            'previous' => $results->first()['value'] ?? 0,
            'result' => $results->last()['value'] ?? 0,
        ];
    }

    private function sessionDurationYesterday(): array
    {
        $results = $this->performQuery('ga:avgSessionDuration', 'ga:date', Period::create(Carbon::yesterday()->clone()->subDay(), Carbon::yesterday()));

        return [
            'previous' => $results->first()['value'] ?? 0,
            'result' => $results->last()['value'] ?? 0,
        ];
    }

    private function sessionDurationLastWeek(): array
    {
        $lastWeek = $this->getLastWeek();
        $currentResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastWeek['current']);
        $previousResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastWeek['previous']);

        return [
            'previous' => $previousResults->pluck('value')[0] ?? 0,
            'result' => $currentResults->pluck('value')[0] ?? 0,
        ];
    }

    private function sessionDurationLastMonth(): array
    {
        $lastMonth = $this->getLastMonth();
        $currentResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastMonth['current']);
        $previousResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastMonth['previous']);

        return [
            'previous' => $previousResults->pluck('value')[0] ?? 0,
            'result' => $currentResults->pluck('value')[0] ?? 0,
        ];
    }

    private function sessionDurationLastSevenDays(): array
    {
        $lastSevenDays = $this->getLastSevenDays();
        $currentResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastSevenDays['current']);
        $previousResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastSevenDays['previous']);

        return [
            'previous' => $previousResults->pluck('value')[0] ?? 0,
            'result' => $currentResults->pluck('value')[0] ?? 0,
        ];
    }

    private function sessionDurationLastThirtyDays(): array
    {
        $lastThirtyDays = $this->getLastThirtyDays();
        $currentResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastThirtyDays['current']);
        $previousResults = $this->performQuery('ga:avgSessionDuration', 'ga:year', $lastThirtyDays['previous']);

        return [
            'previous' => $previousResults->pluck('value')[0] ?? 0,
            'result' => $currentResults->pluck('value')[0] ?? 0,
        ];
    }

    public function ranges(): array
    {
        return [
            1 => __('Today'),
            'Y' => __('Yesterday'),
            'LW' => __('Last Week'),
            'LM' => __('Last Month'),
            7 => __('Last 7 Days'),
            30 => __('Last 30 Days'),
        ];
    }

    public function cacheFor(): \DateTime
    {
         return now()->addMinutes(30);
    }

    public function uriKey(): string
    {
        return 'session-duration';
    }
}
