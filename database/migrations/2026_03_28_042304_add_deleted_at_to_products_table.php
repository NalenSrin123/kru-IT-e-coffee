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
        // ប្រើ Schema::table ដើម្បីកែប្រែ Table ចាស់
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes(); // 🌟 បន្ថែម Field deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes(); // 🌟 លុប Field នេះវិញ ពេលដែលអ្នក rollback
        });
    }
};
