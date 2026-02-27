<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorProfile extends Model
{
    protected $fillable = ['user_id', 'gender'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
