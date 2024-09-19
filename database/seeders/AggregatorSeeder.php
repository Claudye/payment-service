<?php

namespace Database\Seeders;

use App\Models\Aggregator;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AggregatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aggregators = [
            [
                "name" => "Wallet",
                "icon" => "wallet.png",
                "env_keys" => [
                    "env" => "",
                    "public_key" => "",
                    "private_key" => ""
                ],
                "active" => true,
                "info" => "",
                "is_default" => false,
            ],
            [
                "name" => "Fedapay",
                "icon" => "fedapay.png",
                "env_keys" => [
                    "env" => "FEDAPAY_ENV",
                    "public_key" => "FEDAPAY_PUBLIC_KEY",
                    "private_key" => "FEDAPAY_PRIVATE_KEY"
                ],
                "active" => true,
                "info" => "",
                "is_default" => true,
            ],
            [
                "name" => "Kkiapay",
                "icon" => "kkiapay.png",
                "env_keys" => [
                    "env" =>  "KKIAPAY_SANDBOX",
                    "public_key" => "KKIAPAY_PUBLIC_KEY",
                    "private_key" => "KKIAPAY_PRIVATE_KEY"
                ],
                "active" => true,
                "info" => "",
                "is_default" => false,
            ]
        ];

        foreach ($aggregators as $aggregator) {
            $aggregator['slug'] = Str::slug($aggregator['name']);
            Aggregator::create($aggregator);
        }
    }
}
