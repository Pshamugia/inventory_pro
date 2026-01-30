<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LicenseService
{
    public function evaluateCached(): array
    {
        $licenseKey = env('INVENTORY_LICENSE_KEY');
        $deviceId   = DeviceId::get();

        $row = DB::table('license_states')
            ->where('license_key', $licenseKey)
            ->where('device_id', $deviceId)
            ->first();

        if (!$row) {
            return ['ok' => false, 'reason' => 'never_checked'];
        }

        if (!$row->expires_at) {
            return ['ok' => (bool)$row->last_ok, 'reason' => $row->last_reason ?? 'unknown'];
        }

        $expires = Carbon::parse($row->expires_at);
        $graceEnd = $expires->copy()->addDays((int)$row->grace_days);

        $ok = now()->lte($graceEnd);

        return ['ok' => $ok, 'reason' => $ok ? null : ($row->last_reason ?? 'expired')];
    }

    public function checkNow(): array
    {
        $licenseKey = env('INVENTORY_LICENSE_KEY');
        $url = env('LICENSE_SERVER_URL');
        $deviceId = DeviceId::get();

        if (!$licenseKey || !$url) {
            return ['ok' => false, 'reason' => 'not_configured'];
        }

        $now = now();

        try {
            $resp = Http::timeout(5)->post($url, [
                'license_key' => $licenseKey,
                'device_id' => $deviceId,
                'app_version' => '1.0.0',
            ]);

            $json = $resp->json() ?? [];

            $ok = (bool)($json['ok'] ?? false);

            DB::table('license_states')->updateOrInsert(
                ['license_key' => $licenseKey, 'device_id' => $deviceId],
                [
                    'last_check_at' => $now,
                    'last_ok' => $ok ? 1 : 0,
                    'expires_at' => isset($json['expires_at']) ? date('Y-m-d H:i:s', strtotime($json['expires_at'])) : null,
                    'grace_days' => (int)($json['grace_days'] ?? 7),
                    'last_reason' => $json['reason'] ?? null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            return ['ok' => $ok, 'reason' => $json['reason'] ?? null];

        } catch (\Throwable $e) {
            // offline: use cached rules
            return $this->evaluateCached();
        }
    }
}
