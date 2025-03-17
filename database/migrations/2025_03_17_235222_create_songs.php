<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable pgvector extension if it's not already enabled
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('genre')->nullable();
            $table->integer('year')->nullable();
            $table->string('image_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->text('lyrics')->nullable();
            $table->timestamps();
        });

        // Add the embedding vector column (8 dimensions for OpenAI embeddings)
        DB::statement('ALTER TABLE songs ADD COLUMN embedding vector(8)');

        // Create an index for faster similarity searches
        DB::statement('CREATE INDEX ON songs USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
