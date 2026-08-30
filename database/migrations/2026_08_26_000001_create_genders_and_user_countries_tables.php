<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الجنسين (Genders Table)
        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('code')->unique(); // male, female
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // إضافة gender_id إلى user_profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('gender_id')->nullable()->after('gender')->constrained('genders')->nullOnDelete();
        });

        // 2. الجدول الوسيط بين المستخدم والدول (User Countries Pivot Table)
        Schema::create('user_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('target'); // current, target, residence
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_countries');

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);
            $table->dropColumn('gender_id');
        });

        Schema::dropIfExists('genders');
    }
};
