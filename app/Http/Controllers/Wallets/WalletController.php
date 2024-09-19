<?php

namespace App\Http\Controllers\Wallets;

use App\Helpers\Resp;
use Illuminate\Http\Request;
use App\Models\PlatformWallet;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Services\Wallets\WalletService;
use App\Services\Wallets\WalletHistoryService;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function store(Request $request, WalletService $walletService)
    {
        $request->validate([
            "owner_uuid" => "required|uuid",
            "currency_id" => ["required", Rule::exists('currencies', "id")]
        ]);
        $wallet = $walletService->firstOrCreate(
            $request->owner_uuid,
            $request->currency_id
        );

        return Resp::created(WalletResource::make($wallet));
    }

    public function addFund(Request $request, WalletHistoryService $walletService)
    {
        $data = $request->validate([
            "receiver_uuid" => "required|uuid",
            "currency_id" => ["required", Rule::exists('currencies', "id")],
            "amount" => "required|numeric|gt:0",
            "payer_uuid" => "required|uuid"
        ]);

        $platformWallet = PlatformWallet::where('currency_id', $request->currency_id)->first();

        if (!$platformWallet) {
            throw ValidationException::withMessages([
                'currency' => 'Le wallet de la plateforme pour cette devise n\'existe pas.'
            ]);
        }

        if ($platformWallet->amount < $request->amount) {
            throw ValidationException::withMessages([
                'amount' => 'Le montant à ajouter dépasse la quantité disponible dans le wallet de la plateforme.'
            ]);
        }

        $data['note'] = $request->get('note', 'Retrait de fond et ajout au compte de #' . $request->receiver_uuid);
        $wallet = $walletService->addFund($data);

        $userWallet = app(WalletService::class)->findBy([
            'owner_uuid' => $request->receiver_uuid,
            'currency_id' => $request->currency_id
        ]);

        return Resp::success(WalletResource::make($userWallet));
    }
}
