<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupProfile extends Model
{
    protected $fillable = [
        'user_id',
        'access_code_id',
        'project_name',
        'project_domain',
        'leader_name',
        'leader_level',
        'leader_sector',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessCode()
    {
        return $this->belongsTo(AccessCode::class);
    }
}
