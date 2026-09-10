<x-filament-widgets::widget>
    <x-filament::section class="my-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
            AI Summary Analytics
        </h2>

        <div class="esh-summary-content prose prose-sm max-w-none dark:prose-invert" aria-live="polite">
            {!! \Illuminate\Support\Str::markdown(
                $this->paragraph ?? 'Generate with AI a summary of the overall analytics for the past 30 days.',
                ['html_input' => 'strip', 'allow_unsafe_links' => false],
            ) !!}
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <x-filament::button wire:click="summarizeAnalytics" wire:loading.attr="disabled" wire:target="summarizeAnalytics,mostOfConcernFindings,domainExposureAnalysis">
            Summarize Overall Analytics
        </x-filament::button>
        <x-filament::button wire:click="mostOfConcernFindings" wire:loading.attr="disabled" wire:target="summarizeAnalytics,mostOfConcernFindings,domainExposureAnalysis">
            Most of Concern Findings
        </x-filament::button>
        <x-filament::button wire:click="domainExposureAnalysis" wire:loading.attr="disabled" wire:target="summarizeAnalytics,mostOfConcernFindings,domainExposureAnalysis">
            Domain Exposure Analysis
        </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
