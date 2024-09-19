<?php

use App\Models\Currency;
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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            $table->uuid('owner_uuid');

            $table->foreignIdFor(Currency::class)->constrained();

            $table->decimal('amount', 32)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['owner_uuid', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
