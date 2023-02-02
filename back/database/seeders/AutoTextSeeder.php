<?php

namespace Database\Seeders;

use App\Models\AutoText;
use App\Models\User;
use Illuminate\Database\Seeder;

class AutoTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $specialists = User::where('role_id', 5)->get();
        foreach ($specialists as $specialist) {
            AutoText::factory(30)->create(
                [
                    'organization_id' => $specialist->organization_id,
                    'user_id' => $specialist->id
                ]
            );
        }
    }
}
