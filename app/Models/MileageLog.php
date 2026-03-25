<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MileageLog extends Model
{
    protected $fillable = [
        'vehicle_id', 'log_date', 'mileage', 'note',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
