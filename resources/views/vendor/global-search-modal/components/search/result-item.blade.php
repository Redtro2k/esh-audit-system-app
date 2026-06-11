@props([
    'title',
    'rawTitle',
    'group',
    'isLast',
    'url',
    'result',
])

@php
    $details = collect($result->details);
    $avatarUrl = $details->pull('__avatar_url');
    $displayDetails = $details->all();

    $classes = [
        'dark:bg-[--alpha(white_/_3%)] bg-[--alpha(var(--color-gray-900)_/_5%)]',
        'focus-within:dark:bg-[--alpha(white_/_8%)] focus-within:bg-[--alpha(var(--color-gray-900)_/_8%)]',
        'hover:bg-[--alpha(var(--color-gray-900)_/_8%)] dark:hover:bg-[--alpha(white_/_10%)]',
        'my-1 py-2 px-3 duration-300 transition-colors rounded-lg flex justify-between items-center',
    ];

    $isAssoc = \Illuminate\Support\Arr::isAssoc($displayDetails);
@endphp

<li
    {{ $attributes->class(Arr::toCssClasses($classes)) }}
    role="option"
>
    <a
        {{ \Filament\Support\generate_href_html($url) }}
        x-on:keydown.enter.stop="addToSearchHistory(@js($rawTitle), @js($group), @js($url))"
        x-on:click="$data.close();addToSearchHistory(@js($rawTitle), @js($group), @js($url))"
        @class([
            'fi-global-search-result-link block outline-none w-full',
            'pe-4 ps-4 pt-4' => $result->actions,
            'p-3' => ! $result->actions,
        ])
    >
        <div class="flex items-start gap-3">
            @if ($avatarUrl)
                <img
                    src="{{ $avatarUrl }}"
                    alt="{{ $rawTitle }}"
                    class="mt-0.5 h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                    loading="lazy"
                >
            @endif

            <div class="min-w-0 flex-1">
                <h4 class="text-sm text-start font-medium text-gray-950 dark:text-white">
                    <span>
                        {{ str($title)->sanitizeHtml()->toHtmlString() }}
                    </span>
                </h4>

                @if ($displayDetails)
                    <dl class="mt-1">
                        @foreach ($displayDetails as $label => $value)
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex items-start justify-start">
                                @if ($isAssoc)
                                    <dt class="inline shrink-0 font-medium" style="margin-right: 3px; padding-right: 1px;">
                                        {{ $label }}:
                                    </dt>
                                @endif

                                <dd class="inline min-w-0 break-words">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>
    </a>

    @if ($resultVisibleActions = $result->getVisibleActions())
        <div class="fi-global-search-result-actions">
            @foreach ($resultVisibleActions as $action)
                {{ $action }}
            @endforeach
        </div>
    @endif
</li>
