<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->foreignId('current_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->boolean('willing_to_travel')->default(true);
            $table->json('languages')->nullable();
            $table->text('summary')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('user_professions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('user_target_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_target_countries');
        Schema::dropIfExists('user_professions');
        Schema::dropIfExists('user_profiles');
    }
};
