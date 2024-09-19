<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $guarded = ["id"];
    public const TOPUP_TYPE = "topup";

    public const WITHDRAW_TYPE = "withdraw";

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
