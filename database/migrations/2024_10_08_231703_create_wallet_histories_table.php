<?php

use App\Models\WalletHistory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignUuid('wallet_uuid')->constrained(column: "uuid");
            $table->enum('type', ['topup', "withdraw"])->default('topup')->index();
            $table->double('amount')->default(0);
            $table->string('note')->nullable();
            $table->enum('status', WalletHistory::STATUTES)->default('processed');
            $table->uuid('created_by')->nullable();
            $table->foreignId('transaction_id')->constrained();
            $table->unique([
                "transaction_id",
                'wallet_uuid'
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_histories');
    }
};
