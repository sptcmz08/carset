<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanDay extends Model
{
    protected $fillable = [
        'service_date',
        'header_title',
        'timetable_label',
        'footer_notes',
        'note_blocks',
        'handover_blocks',
        'highlight_notice',
    ];

    protected $casts = [
        'service_date' => 'date',
        'note_blocks' => 'array',
        'handover_blocks' => 'array',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ServicePlanEntry::class)->orderBy('display_order');
    }
}