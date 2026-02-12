<x-filament-widgets::widget>
    <x-filament::section class="my-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
            AI Summary Analytics
        </h2>

        <div class="prose prose-sm max-w-none dark:prose-invert">
            {!! \Illuminate\Support\Str::markdown(
                $this->paragraph ?? 'Generate with AI a summary of the overall analytics for the past 30 days.',
                ['html_input' => 'strip', 'allow_unsafe_links' => false],
            ) !!}
        </div>

        <x-filament::button wire:click="summarizeAnalytics" class="mt-4">
            Summarize Overall Analytics
        </x-filament::button>
        <x-filament::button wire:click="mostOfConcernFindings" class="mt-4">
            Most of Concern Findings
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>
