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
        Schema::create('experience_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('code')->unique();
            $table->integer('min_years')->nullable();
            $table->integer('max_years')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('experience_level_id')
                ->nullable()
                ->after('experience_years')
                ->constrained('experience_levels')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['experience_level_id']);
            $table->dropColumn('experience_level_id');
        });

        Schema::dropIfExists('experience_levels');
    }
};
