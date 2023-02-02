<?php

namespace Database\Seeders;

use App\Models\DocumentSection;
use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentTemplate::factory(6)->create();
        DocumentSection::factory(10)->create();
    }
}
