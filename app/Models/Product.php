<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'seo_title',
        'meta_description',
        'long_description',
        'benefits',
        'specifications',
        'usage_tips',
        'seo_tags',
        'rook_job_id',
        'rook_status',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'benefits'       => 'array',
        'specifications' => 'array',
        'usage_tips'     => 'array',
        'seo_tags'       => 'array',
    ];
}