<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteering extends Model
{
    use HasFactory;

    protected $table = 'volunteering';

    protected $fillable = [
        'userName',
        'title',
        'description',
        'compensation_type',
        'compensation_value',
        'image_path',
        'active',
        'approved'
    ];

}
