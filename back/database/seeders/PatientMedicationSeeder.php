<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientMedication;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientMedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $patients = Patient::all();

        foreach ($patients as $patient) {

            for ($i = 0; $i < 6; $i++) {
                if (rand(1, 2) === 1) {

                    PatientMedication::create([
                        'patient_id'    => $patient->id,
                        'name'          =>  $faker->sentence(1),
                        'instructions'   => $faker->sentence(9),
                      
                    ]);
                }
            }
        }
    }
}
