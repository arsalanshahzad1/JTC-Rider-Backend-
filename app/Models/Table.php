<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'title',
        'table_type_id'
    ];

    public function table_type()
    {
        return $this->belongsTo(TableType::class, 'table_type_id', 'id');
    }
}
