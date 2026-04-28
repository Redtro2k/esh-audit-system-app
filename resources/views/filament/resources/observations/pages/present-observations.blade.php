@php
    $observations = $this->getPresentationObservations();
    $observation = $this->getCurrentObservation();
    $totalSlides = $observations->count();
    $currentSlide = $totalSlides > 0 ? $this->slide + 1 : 0;
    $concernImages = $observation ? $this->imageUrls($observation->capture_concern) : [];
    $solvedImages = $observation ? $this->imageUrls($observation->capture_solved) : [];
@endphp

<x-filament-panels::page>
    <div
        x-data="{
            toggleFullscreen() {
                if (document.fullscreenElement) {
                    document.exitFullscreen()

                    return
                }

                this.$refs.stage.requestFullscreen()
            },
        }"
        x-on:keydown.window.arrow-left="$wire.previousSlide()"
        x-on:keydown.window.arrow-right="$wire.nextSlide()"
        x-ref="stage"
        class="presentation-stage relative min-h-[calc(100vh-12rem)] overflow-hidden bg-white p-4 text-gray-950 dark:bg-gray-950 dark:text-white sm:p-6"
    >
        <style>
            .presentation-stage > :not(style):not(.presentation-watermark) {
                position: relative;
                z-index: 1;
            }

            .presentation-watermark {
                position: absolute;
                inset: 0;
                z-index: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: rgb(220 38 38);
                opacity: 0.07;
                pointer-events: none;
            }

            .dark .presentation-watermark {
                color: rgb(248 113 113);
                opacity: 0.1;
            }

            .presentation-watermark img {
                width: min(72rem, 86vw);
                max-height: 70vh;
                object-fit: contain;
            }

            .presentation-stage:fullscreen .presentation-filters {
                display: none;
            }

            .presentation-stage:-webkit-full-screen .presentation-filters {
                display: none;
            }
        </style>

        <div class="presentation-watermark" aria-hidden="true">
            <img src="{{ asset('logo/toyota.png') }}" alt="Toyota logo watermark" />
        </div>

        <div class="presentation-filters border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/80">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div class="flex-1">
                    {{ $this->filtersForm }}
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        Reset
                    </button>
                    <button
                        type="button"
                        x-on:click="toggleFullscreen()"
                        class="rounded-md bg-gray-950 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                    >
                        Fullscreen
                    </button>
                </div>
            </div>
        </div>

        @if (! $observation)
            <div class="flex min-h-[32rem] items-center justify-center px-6 py-16 text-center">
                <div class="max-w-lg">
                    <p class="text-sm font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-300">
                        Presentation Mode
                    </p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                        No observations match these filters.
                    </h2>
                    <p class="mt-3 text-base text-gray-600 dark:text-gray-300">
                        Adjust the filters or reset them to return to the full audit queue.
                    </p>
                </div>
            </div>
        @else
            <div class="flex min-h-[32rem] flex-col">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-sm font-semibold ring-1 {{ $this->statusClass($observation->status) }}">
                            {{ $this->statusLabel($observation->status) }}
                        </span>

                        @if ($this->isOverdue($observation))
                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700 ring-1 ring-red-200 dark:bg-red-900/40 dark:text-red-200 dark:ring-red-700/60">
                                Overdue
                            </span>
                        @endif

                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Slide {{ $currentSlide }} of {{ $totalSlides }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ \App\Filament\Resources\Observations\ObservationResource::getUrl('view', ['record' => $observation]) }}"
                            target="_blank"
                            rel="noreferrer"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            View Record
                        </a>
                        <button
                            type="button"
                            wire:click="previousSlide"
                            @disabled($this->slide === 0)
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            Previous
                        </button>
                        <button
                            type="button"
                            wire:click="nextSlide"
                            @disabled($this->slide >= $totalSlides - 1)
                            class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                        </button>
                    </div>
                </div>

                <div class="grid flex-1 gap-0 xl:grid-cols-[minmax(0,1.15fr)_minmax(24rem,0.85fr)]">
                    <section class="border-b border-gray-200 p-4 dark:border-gray-800 xl:border-b-0 xl:border-r">
                        <div class="grid h-full gap-4 lg:grid-cols-2">
                            <div class="flex min-h-72 flex-col overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-200">
                                    Concern Proof
                                </div>
                                <div class="grid flex-1 gap-2 p-3 {{ count($concernImages) > 1 ? 'sm:grid-cols-2' : '' }}">
                                    @forelse ($concernImages as $imageUrl)
                                        <a href="{{ $imageUrl }}" target="_blank" rel="noreferrer" class="block overflow-hidden rounded-md bg-white dark:bg-gray-950">
                                            <img src="{{ $imageUrl }}" alt="Concern proof" class="h-full min-h-72 w-full object-contain" />
                                        </a>
                                    @empty
                                        <div class="flex min-h-72 items-center justify-center rounded-md bg-white text-sm text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                                            No concern proof uploaded
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex min-h-72 flex-col overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-200">
                                    Solved Proof
                                </div>
                                <div class="grid flex-1 gap-2 p-3 {{ count($solvedImages) > 1 ? 'sm:grid-cols-2' : '' }}">
                                    @forelse ($solvedImages as $imageUrl)
                                        <a href="{{ $imageUrl }}" target="_blank" rel="noreferrer" class="block overflow-hidden rounded-md bg-white dark:bg-gray-950">
                                            <img src="{{ $imageUrl }}" alt="Solved proof" class="h-full min-h-72 w-full object-contain" />
                                        </a>
                                    @empty
                                        <div class="flex min-h-72 items-center justify-center rounded-md bg-white text-sm text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                                            No solved proof uploaded
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6 p-5">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-300">
                                {{ $observation->dealer?->name ?? 'No dealer' }} / {{ $observation->pic?->department?->name ?? 'No department' }}
                            </p>
                            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ $observation->area ?: 'Untitled audit area' }}
                            </h2>
                        </div>

                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">PIC</dt>
                                <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $observation->pic?->name ?? 'No PIC' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Auditor</dt>
                                <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $observation->auditor?->name ?? 'No auditor' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Category</dt>
                                <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $observation->concernType?->name ?? 'No category' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Target Date</dt>
                                <dd class="mt-1 font-semibold {{ $this->isOverdue($observation) ? 'text-red-600 dark:text-red-300' : 'text-gray-950 dark:text-white' }}">
                                    {{ $this->targetDateLabel($observation) }}
                                </dd>
                            </div>
                        </dl>

                        <div class="space-y-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Concern / Remarks</h3>
                            <p class="max-h-52 overflow-y-auto whitespace-pre-line rounded-lg bg-gray-50 p-4 text-base leading-7 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
                                {!!
                                    // $this->plainText($observation->getRawOriginal('concern') ?? $observation->concern) ?: 'No concern details recorded.'
                                    $observation->content ? str($observation->content)->sanitizeHtml() : 'No concern details recorded.';

                                !!}
                            </p>
                        </div>

                        @if (filled($observation->counter_measure))
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Counter Measure</h3>
                                <p class="max-h-40 overflow-y-auto whitespace-pre-line rounded-lg bg-gray-50 p-4 text-base leading-7 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
                                    {{ $this->plainText($observation->counter_measure) }}
                                </p>
                            </div>
                        @endif

                        @if (filled($observation->remarks))
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Remarks</h3>
                                <p class="max-h-40 overflow-y-auto whitespace-pre-line rounded-lg bg-gray-50 p-4 text-base leading-7 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
                                    {{ $this->plainText($observation->remarks) }}
                                </p>
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
