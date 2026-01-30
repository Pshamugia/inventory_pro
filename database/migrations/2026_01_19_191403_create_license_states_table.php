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
    Schema::create('license_states', function (Blueprint $table) {
        $table->id();
        $table->string('license_key', 64);
        $table->string('device_id', 80);

        $table->timestamp('last_check_at')->nullable();
        $table->boolean('last_ok')->default(false);

        $table->timestamp('expires_at')->nullable();
        $table->unsignedInteger('grace_days')->default(7);
        $table->string('last_reason', 40)->nullable();

        $table->timestamps();

        $table->unique(['license_key', 'device_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_states');
    }
};
