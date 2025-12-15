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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();$table->string('barcode')->unique();
            $table->string('filename')->nullable();
            $table->foreignId('playlist_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->boolean('is_favorite')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
