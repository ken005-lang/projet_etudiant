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
        'project_intro',
        'project_image',
        'leader_name',
        'leader_level',
        'leader_sector',
        'project_video',
        'contact_whatsapp',
        'contact_email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessCode()
    {
        return $this->belongsTo(AccessCode::class);
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }
}
