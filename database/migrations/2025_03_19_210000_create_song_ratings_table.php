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
        Schema::create('song_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('song_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // -1 for dislike, 1 for like
            $table->foreignId('recommendation_id')->nullable()->constrained()->onDelete('set null');
            $table->json('source_song_ids')->nullable(); // Songs that led to this recommendation
            $table->timestamps();

            // Each user can rate a song only once
            $table->unique(['user_id', 'song_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_ratings');
    }
};
