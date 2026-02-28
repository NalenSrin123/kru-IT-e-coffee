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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // ប្រើ nullOnDelete ការពារកុំឱ្យវិក្កយបត្របាត់បង់ទិន្នន័យ ពេលយើងលុបកាហ្វេនេះចោលពី Menu នៅថ្ងៃក្រោយ
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_size_id')->nullable()->constrained('product_sizes')->nullOnDelete();

            // ចំណុចពិសេស (Snapshot Data): យើងត្រូវចម្លងឈ្មោះចូលមកផ្ទាល់ ការពារថ្ងៃក្រោយគេដូរឈ្មោះកាហ្វេក្នុង Menu
            $table->string('product_name');
            $table->string('size_name')->nullable();

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
