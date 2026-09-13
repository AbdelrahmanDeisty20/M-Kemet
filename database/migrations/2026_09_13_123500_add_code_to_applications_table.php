<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Application;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        // Generate unique code for existing applications if any exist
        if (Schema::hasTable('applications')) {
            Application::whereNull('code')->orWhere('code', '')->get()->each(function ($app) {
                $app->update([
                    'code' => Application::generateUniqueCode(),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
