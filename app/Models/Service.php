<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name', 
        'description', 
        'price',
        'image_path',
        'reported', 
        'report_reason'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
