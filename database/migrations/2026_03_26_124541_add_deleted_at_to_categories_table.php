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
        // ប្រើ Schema::table (មិនមែន Schema::create ទេ)
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes(); // 🌟 បន្ថែម Field deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes(); // 🌟 លុប Field នេះវិញ ប្រសិនបើអ្នកវាយ command rollback
        });
    }
};
