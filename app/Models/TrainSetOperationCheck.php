<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainSetOperationCheck extends Model
{
    public const DEPARTMENTS = [
        'RST' => 'Rolling Stock',
        'COM' => 'Communication',
        'SIG' => 'Signaling',
    ];

    public const MAINTENANCE_TYPES = [
        'Daily' => 'Daily',
        'Weekly' => 'Weekly',
        'Monthly' => 'Monthly',
        'Yearly' => 'Yearly',
        'Overhaul' => 'Overhaul',
    ];

    protected $fillable = [
        'train_set_id',
        'category',
        'check_key',
        'status',
        'description',
    ];

    public function trainSet(): BelongsTo
    {
        return $this->belongsTo(TrainSet::class);
    }

    public function getCheckLabelAttribute(): string
    {
        return self::DEPARTMENTS[$this->check_key]
            ?? self::MAINTENANCE_TYPES[$this->check_key]
            ?? $this->check_key;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'fit' => 'Fit for',
            'not_fit' => 'Not fit',
            default => '-',
        };
    }
}
