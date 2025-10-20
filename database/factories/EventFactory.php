<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'event_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'event_time' => $this->faker->time('H:i'),
            'location' => $this->faker->address,
            'image' => null,
            'video' => null,
            'csv_file' => null,
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
            'admin_id' => Admin::factory(),
        ];
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => true,
            ];
        });
    }

    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => false,
            ];
        });
    }

    public function upcoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'event_date' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            ];
        });
    }

    public function today()
    {
        return $this->state(function (array $attributes) {
            return [
                'event_date' => now(),
            ];
        });
    }

    public function past()
    {
        return $this->state(function (array $attributes) {
            return [
                'event_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            ];
        });
    }
}
