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
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();

            // ប្រើ nullOnDelete ដូចគ្នានឹង order_items ដែរ
            $table->foreignId('modifier_item_id')->nullable()->constrained('modifier_items')->nullOnDelete();

            // Snapshot ឈ្មោះ
            $table->string('modifier_name');
            $table->decimal('extra_price', 10, 2)->default(0.00);

            $table->timestamps();
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
