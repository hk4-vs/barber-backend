<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopPhotos extends Model
{
    use HasFactory;
    protected $table = 'shop_images';
    protected $fillable = [
        'images',
        "shop_id",
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'shop_id',
    ];
}
