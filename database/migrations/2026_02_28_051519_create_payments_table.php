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
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('payment_method', ['cash', 'khqr', 'card'])->default('cash');
            $table->decimal('amount_paid', 10, 2);

            // សម្រាប់ពេលភ្ញៀវហុចលុយឱ្យ និងលុយអាប់
            $table->decimal('tendered_amount', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->default(0.00);

            $table->string('reference_number')->nullable();
            $table->enum('status', ['completed', 'failed', 'refunded'])->default('completed');

            $table->timestamps();
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
