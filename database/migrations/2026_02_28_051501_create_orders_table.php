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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->unique();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();

            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('next_amount', 10, 2);

            //ENUM field for 
            $table->enum('status',['padding','paid','cancelled'])->default('padding');
            $table->enum('order_type',['dine_in','take_away','delivery'])->default('dine_in');
            $table->enum('payment_status',['pending','paid','cancelled'])->default('pending');
            $table->timestamps();

            $table->text('note')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
