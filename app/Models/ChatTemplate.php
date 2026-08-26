<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTemplate extends Model
{
    protected $table = 'chat_templates';

    protected $fillable = [
        'title',
        'message',
    ];
}
