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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // ភ្ជាប់ទៅកាន់ Table users (1-to-1 Relationship)
            // ប្រើ unique() ដើម្បីធានាថា អតិថិជនម្នាក់មានកន្ត្រក (Cart) សកម្មតែមួយគត់នៅពេលតែមួយ
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
