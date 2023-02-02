<?php

namespace Database\Seeders;

use App\Models\HrmScheduleTimeslot;
use App\Models\PatientAlert;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserRoleSeeder::class,
            OrganizationSeeder::class,
            AppointmentTypeSeeder::class,
            ClinicSeeder::class,
            RoomSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            AnestheticQuestionSeeder::class,
            DoctorAddressBookSeeder::class,
            HrmScheduleTimeslotSeeder::class,
            HrmWeeklyScheduleSeeder::class,
            AppointmentSeeder::class,
            AppointmentTimeRequirementSeeder::class,
            NotificationTemplateSeeder::class,
            DocumentTemplateSeeder::class,
            PatientDocumentSeeder::class,
            PreAdmissionSeeder::class,
            ScheduleFeeSeeder::class,
            BulletinSeeder::class,
            PatientAllergySeeder::class,
            PatientMedicationSeeder::class,
            PatientAlertSeeder::class,
            Icd10CodeSeeder::class,
            AutoTextSeeder::class
           
        ]);
    }
}
