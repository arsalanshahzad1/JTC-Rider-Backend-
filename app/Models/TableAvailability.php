<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableAvailability extends Model
{
    use HasFactory;

    protected $table = 'table_availabilities';

    protected $fillable = [
        'table_id',
        'available_date',
        'available_slot_from',
        'available_slot_to',
        'is_booked'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
