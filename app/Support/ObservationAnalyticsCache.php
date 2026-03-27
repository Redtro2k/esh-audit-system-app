<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ObservationAnalyticsCache
{
    private const VERSION_KEY = 'observations:analytics:version';

    public static function remember(string $namespace, array $context, \DateTimeInterface|\DateInterval|int|null $ttl, Closure $callback): mixed
    {
        $version = Cache::get(self::VERSION_KEY, 'v1');
        $fingerprint = sha1(json_encode(self::normalize($context), JSON_THROW_ON_ERROR));
        $key = "observations:analytics:{$namespace}:{$version}:{$fingerprint}";

        return Cache::remember($key, $ttl ?? now()->addMinutes(10), $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, Str::uuid()->toString());
    }

    private static function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            ksort($value);

            return array_map(self::normalize(...), $value);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }
}
