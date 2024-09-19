<?php

use App\Models\PlatformWallet;
use App\Models\WalletHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignIdFor(PlatformWallet::class)->constrained();
            $table->enum('type', ['topup', "withdraw"])->default('topup')->index();
            $table->double('amount')->default(0);
            $table->string('note')->nullable();
            $table->enum('status', WalletHistory::STATUTES)->default('processed');
            $table->foreignId('transaction_id')->unique()->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_wallet_histories');
    }
};
