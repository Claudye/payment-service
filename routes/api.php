<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{Transactions, Wallets, Platforms};

Route::prefix('transactions')->group(function () {
    Route::get(
        'proceed/{type}/{transaction:uuid}',
        Transactions\ProceedController::class
    )->name('transactions.proceed');

    Route::get(
        "verify/{type}/{transaction:uuid}",
        Transactions\VerifyTransactionController::class
    )->name("transactions.verify");

    Route::get(
        "{transaction:uuid}",
        [Transactions\GetTransactionController::class, "show"]
    );

    Route::match(['PUT', 'POST'], '/payment', Transactions\PaymentController::class);
    Route::match(['PUT', 'POST'], '/withdraw', Transactions\WithdrawController::class);
    Route::match(['PUT', 'POST'], '/transfer', Transactions\TransferController::class);
});


Route::prefix('wallets')->group(function () {
    Route::post('/', [Wallets\WalletController::class, "store"]);
    Route::post('/add-fund', [Wallets\WalletController::class, "addFund"]);
});


Route::prefix("platforms")->group(function () {
    Route::post('/add-fund', [Platforms\WalletController::class, "addFund"]);
});
