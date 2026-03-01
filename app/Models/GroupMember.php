<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = [
        'group_profile_id',
        'name',
        'sector',
        'level',
    ];

    public function group()
    {
        return $this->belongsTo(GroupProfile::class);
    }
}
