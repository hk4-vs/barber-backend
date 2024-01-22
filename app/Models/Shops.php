<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    use HasFactory;
    protected $table = 'shop_details';
    protected $fillable = [
        "shop_owner_id",
        "shop_name",
        "shop_state",
        "shop_city",
        "pincode",
        "shop_address",
        "shop_photo",
    ];
    protected $hidden = [
        'updated_at',
        'created_at',
        'shop_owner_id',
    ];
}
