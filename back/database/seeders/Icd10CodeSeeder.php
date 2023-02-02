<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Icd10Codes;

class Icd10CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // if(($file = fopen(__DIR__."/../icd_10_test_data.csv", "r"))!==FALSE){
        //     while (!feof($file)) {
        //         $row = fgetcsv($file, 0, ",");
        //         // column: icd10_edition,type,code_id,valid,1st_age_flag,
        //         // 1st_age_low,1st_age_high,2nd_age_flag,2nd_age_low,2nd_age_high,
        //         // sex,code,area,preference,additional_code,Op_Room,block,11thmap,
        //         // 10thmap,9thmap,8thmap,7thmap,6thmap,5thmap,4thmap,3rdmap,2ndmap,
        //         // 1stmap,9map,ascii_short_desc,ascii_desc
        //         if(is_array($row) && $row[0] !== "icd10_edition"){
        //             $icd10code = new Icd10Codes();
        //             // field:  id	type	code	short_description	description	created_at	updated_at
        //             $icd10code->type = $row[0] === "ICD 10 DIAG V12" ? "DIAGNOSIS" : "PROCEDURE";
        //             $icd10code->code = $row[2];
        //             $icd10code->short_description = $row[29];
        //             $icd10code->description = $row[30];
        //             $icd10code->save();
        //         }
        //     }
        //     fclose($file);
        // }
    }
}
