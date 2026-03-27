<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class SummaryAnalytics extends Widget
{
    use InteractsWithPageFilters;

    public ?string $paragraph = null;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'livewire.summary-analytics';

    public function getStartDateProperty(): ?string
    {
        return $this->pageFilters['startDate'] ?? now()->startOfMonth()->toDateString();
    }

    public function getEndDateProperty(): ?string
    {
        return $this->pageFilters['endDate'] ?? now()->toDateString();
    }

    public function summarizeAnalytics(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        $this->paragraph = ObservationAnalyticsCache::remember(
            'summary-analytics-text',
            [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ],
            now()->addMinutes(15),
            function () use ($startDate, $endDate): string {
                $previousStartDate = $startDate->copy()->subMonthNoOverflow();
                $previousEndDate = $endDate->copy()->subMonthNoOverflow();

                $observation = Observation::query()
                    ->when($this->startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $this->startDate))
                    ->when($this->endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $this->endDate))
                    ->with('department')
                    ->get();

                $currentTotal = $observation->count();
                $currentPending = $observation->where('status', 'pending')->count();

                $previousMonth = Observation::query()
                    ->whereDate('created_at', '>=', $previousStartDate->toDateString())
                    ->whereDate('created_at', '<=', $previousEndDate->toDateString())
                    ->get();

                $previousTotal = $previousMonth->count();
                $previousPending = $previousMonth->where('status', 'pending')->count();

                $analytics = [
                    'period' => [
                        'start' => $this->startDate,
                        'end' => $this->endDate,
                    ],
                    'total' => $currentTotal,
                    'pending' => $currentPending,
                    'resolved' => $observation->where('status', 'resolved')->count(),
                    'for_discussion' => $observation->where('status', 'for further discussion')->count(),
                    'resolution_rate' => $currentTotal > 0
                        ? round(($observation->where('status', 'resolved')->count() / $currentTotal) * 100, 2)
                        : 0,
                    'avg_resolution_days' => $observation->where('status', 'resolved')->count() > 0
                        ? round($observation->where('status', 'resolved')->avg(
                            fn ($item) => $item->created_at->diffInDays($item->updated_at)
                        ), 2)
                        : 0,
                    'top_departments' => $observation->groupBy(fn ($item) => $item->department?->name ?? 'Unassigned')
                        ->map(fn ($group) => [
                            'name' => $group->first()->department?->name ?? 'Unassigned',
                            'total' => $group->count(),
                        ])
                        ->sortByDesc('total')
                        ->values()
                        ->take(5)
                        ->toArray(),
                    'trend_vs_last_month' => [
                        'total_change_percent' => $this->percentChange($currentTotal, $previousTotal),
                        'pending_change_percent' => $this->percentChange($currentPending, $previousPending),
                        'previous_period' => [
                            'start' => $previousStartDate->toDateString(),
                            'end' => $previousEndDate->toDateString(),
                            'total' => $previousTotal,
                            'pending' => $previousPending,
                        ],
                    ],
                ];

                return $this->generateTextSummary(
                    "You are an internal audit analytics assistant.

                    Analyze the following JSON data and generate a professional executive summary.

                    Rules:
                    - Use only the provided data.
                    - Do not invent missing values.
                    - Comment on performance, workload balance, and trends.
                    - Mention departments with highest findings.
                    - Keep the response under 180 words.
                    - Use a formal and objective tone.

                    JSON Data:
                    " . json_encode($analytics, JSON_PRETTY_PRINT)
                );
            }
        );
    }

    public function mostOfConcernFindings()
    {
        $this->paragraph = ObservationAnalyticsCache::remember(
            'concern-findings-text',
            [],
            now()->addMinutes(15),
            function (): string {
                $observations = Observation::selectRaw('concern_type, count(*) as concern_count')
                    ->whereHas('concernType', fn (Builder $query) => $query->whereNull('parent_id'))
                    ->groupBy('concern_type')
                    ->orderByDesc('concern_count')
                    ->with('concernType')
                    ->get()
                    ->map(fn ($item) => [
                        'concern_type' => $item->concernType?->name ?? 'Unknown',
                        'count' => $item->concern_count,
                    ]);

                return $this->generateTextSummary(
                    "You are an audit analytics assistant.
                    Analyze the parent-level concern distribution and identify which concern domains represent the highest operational exposure.
                    Use only the data provided. Do not assume missing values.

                    Rules:
                    - give me a recommendation on which concern domain should be prioritized for risk mitigation based on the distribution.
                    - Keep the response under 150 words.
                    - Use a formal and objective tone.
                    Json Data: " . json_encode($observations->toArray(), JSON_PRETTY_PRINT)
                );
            }
        );
    }

    public function domainExposureAnalysis()
    {
        $this->paragraph = ObservationAnalyticsCache::remember(
            'domain-exposure-text',
            [],
            now()->addMinutes(15),
            function (): string {
                $observations = Observation::query()
                    ->whereHas('concernType', fn (Builder $query) => $query->whereNull('parent_id'))
                    ->with('concernType')
                    ->get()
                    ->groupBy(fn ($item) => $item->concernType?->name ?? 'Unknown')
                    ->map(fn ($group, $name) => [
                        'concern_type' => $name,
                        'count' => $group->count(),
                    ])
                    ->sortByDesc('count')
                    ->values();

                return $this->generateTextSummary(
                    "You are an Audit Analytics AI.

                    Task:
                    Perform Domain Exposure Analysis using frequency-based aggregation only.

                    Input Data Structure:
                        [
                            { 'concern_type': 'string', 'count': number }
                        ]

                    Instructions:
                        1. Compute total findings.
                        2. Calculate percentage share of each concern_type.
                        3. Identify highest operational exposure.
                        4. Classify exposure using:
                        - High = >40%
                        - Moderate = 20-40%
                        - Low = <20%
                        5. If two or more domains have equal highest count, state they are tied.
                        6. If total findings < 5, mention limited dataset.

                        Rules:
                        - Do NOT invent severity scores.
                        - Do NOT assume missing data.
                        - Base exposure strictly on count.

                        Required Output Format:

                        1. Domain Distribution Table
                        2. Highest Operational Exposure
                        3. Executive Summary (5 sentences max)

                        JSON Data:" . json_encode($observations->toArray(), JSON_PRETTY_PRINT)
                );
            }
        );
    }

    private function generateTextSummary(string $prompt): string
    {
        try {
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
                ->withPrompt($prompt)
                ->generate();

            return $response->text;
        } catch (\Throwable $e) {
            return 'Failed to generate summary: ' . $e->getMessage();
        }
    }

    private function percentChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
