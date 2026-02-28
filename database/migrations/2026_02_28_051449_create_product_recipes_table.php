<?php

use GuzzleHttp\Promise\Create;
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
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();
            //Foreign Keys
            $table->unsignedBigInteger('Product_size_id');
            $table->unsignedBigInteger('ingredient_id');

            $table->decimal('quantity', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            //Foreign Key Constraints
            $table->foreign('Product_size_id')->references('id')->on('product_sizes')->onDelete('cascade');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_recipes');
    }
};
