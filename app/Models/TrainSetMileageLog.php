<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainSetMileageLog extends Model
{
    protected $fillable = [
        'train_set_id',
        'log_date',
        'mileage',
        'note',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function trainSet(): BelongsTo
    {
        return $this->belongsTo(TrainSet::class);
    }
}