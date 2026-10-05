<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(?int $userId, string $action, string $module, ?int $recordId = null, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
