<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aggregator extends Model
{
    use HasFactory;

    public static function slugs(): array
    {
        $aggregators = Cache::get('aggregators');

        if (!$aggregators) {
            $aggregators = Aggregator::select(['slug', 'active'])
                ->where("active", true)
                ->get()
                ->pluck('slug')
                ->all();
            Cache::put('aggregators', $aggregators);
        }

        return $aggregators;
    }

    public function casts()
    {
        return [
            "active" => "boolean",
            "env_keys" => "array"
        ];
    }

    public function loadPublicCredentials()
    {
        $options = [
            "env" => env(data_get($this->env_keys, "env", "NULL")),
            "public_key" => env(data_get($this->env_keys, "public_key", "NULL"))
        ];

        return $this->setAttribute('options', $options);
    }

    public function isWallet()
    {
        return $this->slug == "wallet";
    }

    public static function wallet(): Aggregator
    {
        return static::where('slug', 'wallet')->first();
    }
}
