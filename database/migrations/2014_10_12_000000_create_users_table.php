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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ភ្ជាប់ទៅកាន់ Table roles (កម្រិតសិទ្ធិ ឧ. Admin, Cashier, Customer)
            // restrictOnDelete(): ការពារមិនឱ្យ Admin លុប Role ចោលផ្តេសផ្តាស បើ Role នោះកំពុងមាន User ប្រើប្រាស់
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();

            $table->string('name');
            $table->string('email')->unique();

            // ដាក់ nullable() ព្រោះបើគាត់ Login តាម Google គាត់មិនមាន Password ក្នុងប្រព័ន្ធយើងទេ
            $table->string('password')->nullable();
            $table->string('avatar_url')->nullable();

            // នេះគឺជាកន្លែងដែលយើងប្រើប្រាស់ Enum auth_provider ដែលអ្នកបានសួរតាំងពីដំបូង!
            $table->enum('provider', ['local', 'google'])->default('local');

            // ទុកសម្រាប់ផ្ទុកលេខកូដសម្គាល់ (ID) ដែល Google បោះមកឱ្យពេល Login ជោគជ័យ
            $table->string('provider_id')->nullable();

            // Laravel ទាមទារ column នេះសម្រាប់មុខងារ "Remember Me" ពេល Login
            $table->rememberToken();

            // ទុកកត់ត្រាថាគាត់ Login ចូល App ចុងក្រោយនៅម៉ោងប៉ុន្មាន
            $table->timestamp('last_login_at')->nullable();

            // សម្រាប់បិទគណនី (ឧ. User នេះត្រូវបាន Block មិនឱ្យកុម្ម៉ង់ទៀតទេ)
            $table->boolean('is_active')->default(true);

            // បង្កើត Column `deleted_at` សម្រាប់មុខងារ SoftDeletes របស់ Laravel
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
