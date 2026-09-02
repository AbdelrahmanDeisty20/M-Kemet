<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('code')->nullable()->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('qualification_id')
                ->nullable()
                ->after('current_country_id')
                ->constrained('qualifications')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['qualification_id']);
            $table->dropColumn('qualification_id');
        });

        Schema::dropIfExists('qualifications');
    }
};
