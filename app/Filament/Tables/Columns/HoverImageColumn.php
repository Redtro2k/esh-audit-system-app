<?php

namespace App\Filament\Tables\Columns;

use Illuminate\Support\Js;
use TinusG\FilamentHoverImageColumn\HoverImageColumn as BaseHoverImageColumn;

class HoverImageColumn extends BaseHoverImageColumn
{
    public function toEmbeddedHtml(): string
    {
        $baseHtml = parent::toEmbeddedHtml();

        if (! $this->isPreviewEnabled()) {
            return $baseHtml;
        }

        $previewUrl = $this->resolvePreviewUrl();

        if (blank($previewUrl)) {
            return $baseHtml;
        }

        $maxWidth = $this->getPreviewMaxWidth();
        $maxHeight = $this->getPreviewMaxHeight();
        $boundarySize = $this->getPreviewBoundarySize();
        $urlJs = Js::from($previewUrl);

        ob_start(); ?>

        <div
            x-data="{
                show: false,
                x: 0,
                y: 0,
                defaultUrl: <?= $urlJs ?>,
                url: <?= $urlJs ?>,
                updateFromTarget(event) {
                    const img = event.target.closest('img');

                    if (img) {
                        this.url = img.currentSrc || img.src || this.defaultUrl;
                    }
                },
                updatePosition(event) {
                    this.updateFromTarget(event);
                    const offset = 16;
                    const boundary = <?= $boundarySize ?>;
                    let x = event.clientX + offset;
                    let y = event.clientY + offset;
                    if (x + boundary > window.innerWidth) x = event.clientX - boundary - offset;
                    if (y + boundary > window.innerHeight) y = event.clientY - boundary - offset;
                    if (x < 0) x = offset;
                    if (y < 0) y = offset;
                    this.x = x;
                    this.y = y;
                }
            }"
            @mouseenter="updatePosition($event); show = true"
            @mousemove.throttle.50ms="updatePosition($event)"
            @mouseleave="show = false"
        >
            <?= $baseHtml ?>

            <template x-teleport="body">
                <div
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    :style="`position: fixed; left: ${x}px; top: ${y}px; z-index: 50;`"
                    class="pointer-events-none"
                    x-cloak
                >
                    <div
                        style="padding: 4px; border-radius: 8px; overflow: hidden;"
                        class="bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <img
                            :src="show ? url : ''"
                            style="max-width: <?= e($maxWidth) ?>; max-height: <?= e($maxHeight) ?>; border-radius: 6px; display: block;"
                            alt=""
                        />
                    </div>
                </div>
            </template>
        </div>

        <?php return ob_get_clean();
    }
}
