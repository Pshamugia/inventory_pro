<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         if (!Schema::hasColumn('units', 'symbol')) {
        Schema::table('units', function (Blueprint $table) {
            $table->string('symbol')->nullable()->after('name');
        });
         }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('symbol');
        });
    }
};
