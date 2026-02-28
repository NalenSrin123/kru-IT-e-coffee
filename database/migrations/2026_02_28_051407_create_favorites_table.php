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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            // ភ្ជាប់ទៅកាន់ Table users
            // បើ User ត្រូវលុប នោះទិន្នន័យ Favorite របស់គាត់ក៏ត្រូវលុបចោលដែរ
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // ភ្ជាប់ទៅកាន់ Table products
            // បើ Admin លុបភេសជ្ជៈនេះចោលពីប្រព័ន្ធ វាក៏នឹងត្រូវលុបចេញពីបញ្ជី Favorite របស់អតិថិជនទាំងអស់ដូចគ្នា
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // [ចំណុចបន្ថែមពិសេស] ការពារកុំឱ្យ User ម្នាក់ចុចបេះដូង (Favorite) លើភេសជ្ជៈមួយមុខដដែលៗ ២ដង
            $table->unique(['user_id', 'product_id']);

            // បង្កើត created_at និង updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
