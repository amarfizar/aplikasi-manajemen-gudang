<?php

namespace App\Services;

use App\Models\TransactionCounter;
use Illuminate\Support\Facades\DB;

class TransactionNumberGenerator
{
    public function next(string $type): string
    {
        return DB::transaction(function () use ($type) {
            $date = now()->format('Ymd');

            $counter = TransactionCounter::firstOrCreate(
                ['type' => $type, 'date' => $date],
                ['last_number' => 0]
            );

            $counter = TransactionCounter::whereKey($counter->id)->lockForUpdate()->first();
            $counter->increment('last_number');

            $prefix = match ($type) {
                'in' => 'IN',
                'out' => 'OUT',
                'adjustment' => 'ADJ',
                default => strtoupper($type),
            };

            return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $counter->last_number, 4, '0', STR_PAD_LEFT);
        });
    }
}
