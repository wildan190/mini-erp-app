<?php

namespace App\Octane\Listeners;

use Illuminate\Support\Facades\Log;
use Laravel\Octane\Events\RequestHandled;

/**
 * Log a warning when a worker's memory climbs above a threshold.
 *
 * This does NOT fix a leak — it makes leaks visible in logs before the
 * container OOM-killer fires. Set OCTANE_MEMORY_WARN_MB in .env.docker
 * to tune the threshold (default 100MB).
 */
class WarnHighMemoryUsage
{
    public function handle(RequestHandled $event): void
    {
        $usageMb    = memory_get_usage(true) / 1024 / 1024;
        $thresholdMb = (int) env('OCTANE_MEMORY_WARN_MB', 100);

        if ($usageMb >= $thresholdMb) {
            Log::warning('Octane worker high memory usage', [
                'usage_mb'      => round($usageMb, 2),
                'threshold_mb'  => $thresholdMb,
                'peak_mb'       => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'path'          => $event->request->path(),
            ]);
        }
    }
}
