<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organization;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationPatient>
 */
class DocumentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'organization_id'   => Organization::inRandomOrder()->first()->id,
            'title'             => $this->faker->sentence(),
            'type'              => $this->faker->randomElement(['REPORT', 'REFERRAL', 'CLINICAL_NOTE', 'LETTER']),
        ];
    }
}
