<?php

namespace App\Services;

class DeviceId
{
    public static function get(): string
    {
        $path = storage_path('app/device_id.txt');

        if (file_exists($path)) {
            return trim((string) file_get_contents($path));
        }

        $id = bin2hex(random_bytes(16));
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $id);

        return $id;
    }
}
