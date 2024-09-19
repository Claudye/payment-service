<?php

use App\Models\Currency;
use App\Models\Transaction;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('service_name');
            $table->string('service_id')->nullable();

            $table->decimal('amount', 32);
            $table->uuid('payer_uuid');
            $table->uuid('receiver_uuid')->nullable();

            $table->foreignId("aggregator_id")->constrained();
            $table->string('note', 255)->nullable();

            $table->timestamp('perform_time')->nullable();
            $table->timestamp('refund_time')->nullable();
            $table->string("transaction_id", length: 191);

            $table->enum('status', Transaction::STATUSES)
                ->default(Transaction::STATUS_PROGRESS)
                ->index();

            $table->json("data")->nullable();

            $table->enum('type', Transaction::TYPES)
                ->default("payment")
                ->index();

            $table->foreignIdFor(Currency::class)->constrained();

            $table->unique(["aggregator_id", "transaction_id"]);
            $table->string('status_description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
