<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRequest extends Model
{
    use HasFactory;

    protected $table = 'customer_requests';

    protected $fillable = [
        'rider_id',
        'order_id',
        'customer_id',
        'warehouse_id'
    ];
}