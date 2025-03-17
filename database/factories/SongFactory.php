<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Song>
 */
class SongFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Song::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create dummy embedding with 8 dimensions (based on migration)
        $embedding = [];
        for ($i = 0; $i < 8; $i++) {
            $embedding[] = $this->faker->randomFloat(6, -1, 1);
        }

        return [
            'title' => $this->faker->sentence(3),
            'artist' => $this->faker->name(),
            'album' => $this->faker->boolean(80) ? $this->faker->words(3, true) : null,
            'genre' => $this->faker->randomElement(['Rock', 'Pop', 'Hip Hop', 'R&B', 'Jazz', 'Classical', 'Electronic', 'Country', 'Folk', null]),
            'year' => $this->faker->numberBetween(1950, 2024),
            'image_url' => $this->faker->boolean(70) ? $this->faker->imageUrl(300, 300, 'music') : null,
            'preview_url' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'lyrics' => $this->faker->boolean(70) ? $this->faker->paragraphs(3, true) : null,
            'embedding' => json_encode($embedding),
        ];
    }

    /**
     * Define a state for songs with lyrics.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withLyrics()
    {
        return $this->state(fn (array $attributes) => [
            'lyrics' => $this->faker->paragraphs(5, true),
        ]);
    }

    /**
     * Define a state for classical music.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function classical()
    {
        return $this->state(fn (array $attributes) => [
            'genre' => 'Classical',
            'artist' => $this->faker->randomElement([
                'Ludwig van Beethoven', 'Wolfgang Amadeus Mozart', 'Johann Sebastian Bach',
                'Frédéric Chopin', 'Franz Schubert', 'Claude Debussy', 'Johannes Brahms',
                'Giuseppe Verdi', 'Pyotr Ilyich Tchaikovsky', 'Richard Wagner'
            ]),
        ]);
    }

    /**
     * Define a state for recent songs (2020+).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function recent()
    {
        return $this->state(fn (array $attributes) => [
            'year' => $this->faker->numberBetween(2020, 2024),
        ]);
    }
}
