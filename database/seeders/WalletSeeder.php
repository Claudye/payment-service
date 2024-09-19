<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\PlatformWallet; // Assurez-vous d'importer le modèle PlatformWallet

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Currency::all() as $currency) {
            PlatformWallet::create([
                'uuid' => Str::uuid(), // Générer un UUID unique
                'currency_id' => $currency->id, // Associer le wallet à la devise
                'amount' => 0, // Initialiser le montant à 0 ou à une valeur par défaut
            ]);
        }
    }
}
