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
        Schema::create('modifier_recipes', function (Blueprint $table) {
            $table->id();

            // ភ្ជាប់ទៅកាន់ Table modifier_items (ជម្រើសលម្អិត ឧទាហរណ៍៖ "ថែមគុជ")
            $table->foreignId('modifier_item_id')->constrained('modifier_items')->cascadeOnDelete();

            // ភ្ជាប់ទៅកាន់ Table ingredients (វត្ថុធាតុដើម ឧទាហរណ៍៖ "គុជស្ងោរស្រាប់")
            // ចំណុចប្រុងប្រយ័ត្ន៖ បើសិនវត្ថុធាតុដើមនេះត្រូវលុប យើងឱ្យវាលុបរូបមន្តនេះចោលតាមដែរ
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();

            // ចំនួនដែលត្រូវកាត់ចេញពីស្តុក (ឧទាហរណ៍៖ 50)
            // ឯកតារង្វាស់ (UOM) គឺអាស្រ័យលើអ្វីដែលបានកំណត់ក្នុង Table ingredients (ឧ. 50 ក្រាម)
            $table->decimal('quantity', 10, 2);

            // ទោះក្នុង Schema ដើមមានតែ created_at តែការប្រើ timestamps() គឺល្អបំផុតសម្រាប់ Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_recipes');
    }
};
