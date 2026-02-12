<?php

namespace App\Livewire;

use App\Models\Observation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Widget;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Filament\Widgets\Concerns\InteractsWithPageFilters;


class SummaryAnalytics extends Widget
{
    use InteractsWithPageFilters;

    public ?string $paragraph = null;

    protected int | string | array $columnSpan = 'full';

    public function getStartDateProperty(): ?string
    {
        return $this->pageFilters['startDate'] ?? now()->startOfMonth()->toDateString();
    }

    public function getEndDateProperty(): ?string
    {
        return $this->pageFilters['endDate'] ?? now()->toDateString();
    }

    private function percentChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function summarizeAnalytics(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        $previousStartDate = $startDate->copy()->subMonthNoOverflow();
        $previousEndDate = $endDate->copy()->subMonthNoOverflow();

        $observation = Observation::query()
            ->when($this->startDate, function (Builder $query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function (Builder $query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
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
            'resolution_rate' => $observation->count() > 0 ? round(($observation->where('status', 'resolved')->count() / $observation->count()) * 100, 2) : 0,
            'avg_resolution_days' => $observation->where('status', 'resolved')->count() > 0 ? round($observation->where('status', 'resolved')->avg(function ($item) {
                return $item->created_at->diffInDays($item->updated_at);
            }), 2) : 0,
            'top_departments' => $observation->groupBy(fn ($item) => $item->department?->name ?? 'Unassigned')
                ->map(function ($group) {
                    return [
                        'name' => $group->first()->department?->name ?? 'Unassigned',
                        'total' => $group->count(),
                    ];
                })
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
        $prompt = "
        You are an internal audit analytics assistant.

        Analyze the following JSON data and generate a professional executive summary.

        Rules:
        - Use only the provided data.
        - Do not invent missing values.
        - Comment on performance, workload balance, and trends.
        - Mention departments with highest findings.
        - Keep the response under 180 words.
        - Use a formal and objective tone.

        JSON Data:
        " . json_encode($analytics, JSON_PRETTY_PRINT);

        try {
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
                ->withPrompt($prompt)
                ->generate();

            $this->paragraph = $response->text;
        } catch (\Throwable $e) {
            $this->paragraph = 'Failed to generate summary: ' . $e->getMessage();
        }
    }

    public function mostOfConcernFindings()
    {
        $observations = Observation::selectRaw('concern_type, count(*) as concern_count')
            ->whereHas('concernType', function (Builder $query) {
                $query->whereNull('parent_id');
            })
            ->groupBy('concern_type')
            ->orderByDesc('concern_count')
            ->with('concernType')
            ->get()
            ->map(function ($item) {
                return [
                    'concern_type' => $item->concernType?->name ?? 'Unknown',
                    'count' => $item->concern_count,
                ];
            });

            $prompt = "
            You are an audit analytics assistant.
            Analyze the parent-level concern distribution and identify which concern domains represent the highest operational exposure.
            Use only the data provided. Do not assume missing values.

            Rules:
            - give me a recommendation on which concern domain should be prioritized for risk mitigation based on the distribution.
            - Keep the response under 150 words.
            - Use a formal and objective tone.
            Json Data: " . json_encode($observations->toArray(), JSON_PRETTY_PRINT);

             try {
                $response = Prism::text()
                    ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
                    ->withPrompt($prompt)
                    ->generate();

                $this->paragraph = $response->text;
            } catch (\Throwable $e) {
                $this->paragraph = 'Failed to generate summary: ' . $e->getMessage();
            }
    }

    protected string $view = 'livewire.summary-analytics';
}
