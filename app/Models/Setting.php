<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ["fees", "user_id", "subscription_option"];

    public static function setting()
    {
        return self::first();
    }
}
