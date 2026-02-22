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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->default('Anmi Beatz');
            $table->text('description')->nullable();
            $table->string('genre');
            $table->string('image_url');
            $table->string('audio_url');
            $table->string('youtube_link')->nullable();
            $table->string('spotify_link')->nullable();
            $table->integer('plays')->default(0);
            $table->string('duration');
            $table->date('release_date');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
