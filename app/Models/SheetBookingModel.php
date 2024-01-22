<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetBookingModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'time',
        'user_name',
        'order',
        "shop_id",
        "service_id"
    ];
    protected $hidden = ["created_at", "updated_at"];
}
