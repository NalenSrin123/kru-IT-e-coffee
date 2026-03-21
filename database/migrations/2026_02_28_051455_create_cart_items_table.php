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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // ដាក់ nullable ព្រោះទំនិញខ្លះអាចអត់មានទំហំ (ឧ. នំកញ្ចប់)
            $table->foreignId('product_size_id')->nullable()->constrained('product_sizes')->nullOnDelete();

            $table->integer('quantity')->default(1);

            // ចំណុចពិសេស៖ ប្រើប្រភេទទិន្នន័យ JSON ដើម្បីផ្ទុក Modifiers ឱ្យស្រាល Database ពេលអតិថិជនកំពុងរើស
            $table->json('modifiers_json')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
