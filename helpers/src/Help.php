<?php

namespace Helpers;

use App\Models\Rate;
use App\Models\Setting;

class Help
{

    public static function commission(Rate $rate, $amount)
    {
        return $rate->value * $amount / 100;
    }

    public static function alert($message, $type = "error")
    {
        return [
            "message" => $message,
            "type" => $type,
        ];
    }
}
