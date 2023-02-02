<?php

namespace Database\Factories;


use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\UserRole;
use App\Models\Organization;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $organization = Organization::inRandomOrder()->first();

        if (empty($organization)) {
            $organizationId = 0;
        } else {
            $organizationId = $organization->id;
        }

        $userName = $this->faker->unique()->username();
        $roleId = UserRole::where('id', '<>', 2)->inRandomOrder()->first()->id;

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'username' => $userName,
            'role_id' => $roleId,
            'email' => $this->faker->unique()->safeEmail(),
            'mobile_number' => $this->faker->numerify('0#-####-####'),
            'date_of_birth' => $this->faker->date(),
            'email_verified_at' => now(),
            'password' => Hash::make('Paxxw0rd'),
            'remember_token' => Str::random(10),
            'organization_id' => $organizationId,
            'address' => $this->faker->address(),
            'password_changed_date' => date('Y-m-d H:i:s'),
            'outside_hours' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
