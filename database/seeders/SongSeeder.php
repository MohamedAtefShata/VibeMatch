<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 random songs
        Song::factory(50)->create();

        // Create 10 classical music songs
        Song::factory(10)->classical()->create();

        // Create 20 recent songs with lyrics
        Song::factory(20)->recent()->withLyrics()->create();
    }
}
