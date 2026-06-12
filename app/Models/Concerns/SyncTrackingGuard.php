<?php

namespace App\Models\Concerns;

/**
 * Shared guard so applying incoming sync changes (in SyncController) can
 * suppress sync_outbox writes across every model using the Syncable trait
 * (PHP trait statics are per-class, so this lives outside the trait).
 */
class SyncTrackingGuard
{
    private static int $depth = 0;

    public static function run(callable $callback): mixed
    {
        static::$depth++;

        try {
            return $callback();
        } finally {
            static::$depth--;
        }
    }

    public static function isDisabled(): bool
    {
        return static::$depth > 0;
    }
}
