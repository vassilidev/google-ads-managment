<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads', static function (Blueprint $table) {
            $table->id();
            $table->string('ad_id')->nullable();
            $table->string('name')->nullable();
            $table->string('target_cpa')->nullable();
            $table->string('spend_budget')->nullable();
            $table->string('budget')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
