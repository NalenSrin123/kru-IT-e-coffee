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
        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('modifier_item_id');

            // Snapshot (important)
            $table->string('modifier_name');
            $table->decimal('extra_price', 10, 2)->default(0.00);


            // Foreign Key Constraints
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('modifier_item_id')->references('id')->on('modifier_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
    }
};
