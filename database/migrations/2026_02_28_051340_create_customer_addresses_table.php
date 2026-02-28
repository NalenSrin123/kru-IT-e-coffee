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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            // ភ្ជាប់ទៅកាន់ Table users (1-to-Many Relationship)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('title'); // ឈ្មោះចំណាំ ឧទាហរណ៍៖ "ផ្ទះ", "កន្លែងធ្វើការ", "ផ្ទះមិត្តភក្តិ"
            $table->string('address_line'); // អាសយដ្ឋានលំអិត
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false); // សម្រាប់កំណត់ថាជាអាសយដ្ឋានដែលគាត់ចង់ឱ្យលោតចេញមុនគេពេល Checkout
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
