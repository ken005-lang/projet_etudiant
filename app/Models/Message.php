<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'visitor_id',
        'group_id',
        'visitor_message',
        'group_reply',
        'is_read_by_group',
        'is_read_by_visitor'
    ];

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }

    public function group()
    {
        return $this->belongsTo(User::class, 'group_id');
    }
}
