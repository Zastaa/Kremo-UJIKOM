<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class ApplySimulatedTime
{
    public const SETTING_FILE = 'framework/dev-simulated-now.txt';

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        $simulatedNow = self::simulatedAt();

        if (! $simulatedNow) {
            return $next($request);
        }

        Carbon::setTestNow($simulatedNow);

        try {
            return $next($request);
        } finally {
            Carbon::setTestNow();
        }
    }

    public static function store(Carbon $simulatedAt): void
    {
        File::ensureDirectoryExists(dirname(self::path()));
        File::put(self::path(), $simulatedAt->seconds(0)->format('Y-m-d H:i:s'));
    }

    public static function clear(): void
    {
        File::delete(self::path());
    }

    public static function simulatedAt(): ?Carbon
    {
        if (! File::exists(self::path())) {
            return null;
        }

        $value = trim((string) File::get(self::path()));

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'));
        } catch (\Throwable) {
            self::clear();

            return null;
        }
    }

    private static function path(): string
    {
        return storage_path(self::SETTING_FILE);
    }
}
