<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'UserName',
        'name',
        'description',
        'price',
        'image_path',
        'approved',
        'available',
        'reported', 
        'report_reason'
    ];
}
