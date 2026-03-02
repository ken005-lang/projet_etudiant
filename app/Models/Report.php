<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'group_profile_id',
        'file_name',
        'file_path',
    ];

    public function groupProfile()
    {
        return $this->belongsTo(GroupProfile::class);
    }
}
