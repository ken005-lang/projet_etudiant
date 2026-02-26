<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    protected $fillable = ['code', 'is_used'];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
        ];
    }
}
