<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOwners extends Model
{
    use HasFactory;
    protected $table = 'shop_owners';
    protected $fillable = [
        "user_id",
        "phone",
        "gender",
        "address",
        "owner_photo",
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'user_id',
    ];
}
