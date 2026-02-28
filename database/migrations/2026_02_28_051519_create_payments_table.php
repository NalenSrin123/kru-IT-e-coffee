<?php

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('cashier_id');

            // ENUM Payment Method
            $table->enum('payment_method', ['cash', 'card', 'khqr'])->default('cash');

            $table->decimal('amount_paid', 10, 2);

            // POS Cash Handling
            $table->decimal('tendered_amount', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->default(0.00);

            $table->string('reference_number')->nullable();

            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                  ->default('completed');

            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
