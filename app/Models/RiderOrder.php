<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderOrder extends Model
{
    use HasFactory;

    protected $table = 'rider_orders';

    protected $fillable = [
        'rider_id',
        'order_id',
        'customer_id',
        'warehouse_id',
        'status'
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}