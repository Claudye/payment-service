<?php

namespace App\Http\Controllers\Platforms;

use App\Helpers\Resp;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WalletHistory;
use App\Models\PlatformWallet;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\PlatformWalletHistory;
use App\Http\Resources\WalletResource;
use App\Services\Wallets\WalletHistoryService;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{

    public function addFund(Request $request, WalletHistoryService $walletService)
    {
        $data = $request->validate([
            "currency_id" => ["required", Rule::exists('currencies', "id")],
            "amount" => "required|numeric|gt:0",
        ]);

        $platformWallet = PlatformWallet::where('currency_id', $request->currency_id)->first();

        if (!$platformWallet) {
            throw ValidationException::withMessages([
                'currency' => 'Le wallet de la plateforme pour cette devise n\'existe pas.'
            ]);
        }
        // Créer l'historique pour un retrait (diminution de fonds)
        PlatformWalletHistory::create([
            'uuid' => Str::uuid(),
            'platform_wallet_id' => $platformWallet->id,
            'type' => Wallet::TOPUP_TYPE,
            'amount' => $request->amount,
            'note' => "Ajout de fonds",
            'status' => WalletHistory::PROCESSED,
        ]);

        // Mettre à jour le montant du wallet de la plateforme
        $platformWallet->increment('amount', $request->amount);

        return Resp::success(WalletResource::make($platformWallet));
    }
}
