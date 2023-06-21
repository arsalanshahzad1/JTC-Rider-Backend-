<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableType extends Model
{
    use HasFactory;

    protected $table = 'table_types';

    protected $fillable = ['title'];

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
