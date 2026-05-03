<?php

namespace App\Services;

use App\Models\ScanLog;
use App\Models\ScanToken;
use Illuminate\Support\Str;

class ScannerService
{
    /**
     * Generate a unique scan token for an entity.
     */
    public function generateToken(string $type, int $id, ?int $expiryHours = null): ScanToken
    {
        // Reuse existing non-expired token if available
        $existing = ScanToken::where('tokenable_type', $type)
            ->where('tokenable_id', $id)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        return ScanToken::create([
            'token' => Str::random(64),
            'tokenable_type' => $type,
            'tokenable_id' => $id,
            'expired_at' => $expiryHours ? now()->addHours($expiryHours) : null,
        ]);
    }

    /**
     * Resolve a token to its associated entity.
     */
    public function resolveToken(string $token): ?ScanToken
    {
        return ScanToken::where('token', $token)->first();
    }

    /**
     * Log a scan activity.
     */
    public function logScan(string $token, string $action, ?int $userId = null, ?string $type = null, ?int $id = null): ScanLog
    {
        return ScanLog::create([
            'user_id' => $userId,
            'token' => $token,
            'scannable_type' => $type,
            'scannable_id' => $id,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
