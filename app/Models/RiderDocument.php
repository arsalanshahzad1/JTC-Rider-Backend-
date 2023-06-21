<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderDocument extends Model
{
    use HasFactory;

    protected $table = 'rider_documents';

    public function getDocumentsAttribute($value)
	{
	    return json_decode($value);
	}
}
