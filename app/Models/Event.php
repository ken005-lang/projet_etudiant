<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'event_date',
        'description',
        'image_path',
        'video_path',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
        ];
    }
}
