<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DocumentTemplate;


class DocumentSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'template_id'       => DocumentTemplate::inRandomOrder()->first()->id,
            'title'             => $this->faker->sentence(),
            'free_text_default' => $this->faker->sentence(),
            'is_image'          => $this->faker->boolean(),
        ];
    }
}
