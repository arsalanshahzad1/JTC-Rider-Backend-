<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $table = 'user_logs';

    public function user_log()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
