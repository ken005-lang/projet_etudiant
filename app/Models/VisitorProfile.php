<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorProfile extends Model
{
    protected $fillable = ['user_id', 'first_name', 'last_name', 'gender', 'email'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
