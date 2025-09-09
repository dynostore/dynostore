<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('csv_log')) {
    /**
     * Emit: SERVICE,OPERATION,OBJECTKEY,START/END,Status,MSG
     * @param string $level debug|info|warning|error|critical
     */
    function csv_log(
        string $service,
        string $operation,
        string $objectKey,
        string $phase,
        string $status,
        string $msg = '',
        string $level = 'debug'
    ): void {
        $line = "{$service},{$operation},{$objectKey},{$phase},{$status},{$msg}";
        Log::channel('csv')->{$level}($line);
    }
}
