<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class ReportVAEDController extends Controller
{

    public function generateVAEDforEpisode(Request $request)
    {

        return  '<pre>' . $this->generateEpisodeVAEDString(Appointment::find(30)) . '</pre>';
    }

    public function generateEpisodeVAEDString(Appointment $appointment)
    {
        $patient = $appointment->patient;
        // Generated from oage 11 - 13 of VAED manual Section 5

        $transactionType = "E5";               // len 2, E5
        $uniqueKey = str_pad($appointment->id, 9, 0, STR_PAD_LEFT); //len 9, AAAAAAAAA
        $patientIdentifier = str_pad($patient->id, 10, 0, STR_PAD_LEFT); // len 10, AAAAAAAAAA
        $campusCode = "2222";                         // len 4, NNNN
        $medicareNumber = "11111111111";              // len 11,NNNNNNNNNNN
        $medicareSuffix = str_pad(substr($patient->first_name, 0, 3), 3, " "); // len 3, AAA or A-A
        $sex = $patient->gender;                       // len 1, N
        $maritalStatus = $patient->marital_status;    // len 1, N
        $dateOfBirth = substr($patient->date_of_birth, -2) . substr($patient->date_of_birth, 5, 2) . substr($patient->date_of_birth, 0, 4);   // len 8, DDMMYYYY
        $postcode = $patient->postcode;                // len 4, NNNN
        $locality = str_pad($patient->suburb, 22, ' '); //len 22. Alphanumeric, left justified
        $admissionDate = substr($appointment->date, -2) . substr($appointment->date, 5, 2) . substr($appointment->date, 0, 4); // len 8, DDMMYYYY
        $admissionTime = substr(str_replace(':', '', $appointment->start_time), 0, 4);   // len 4, HHMM
        $admissionType = $appointment->detail->vaed_admission_type;  //len 1, A
        $admissionSource = $appointment->detail->vaed_admission_source;  //len 1, A
        $transferSource = "    "; // leng 4, NNNN,
        $leaveWithPermissionDaysMTD = "00"; // leng 1, NN
        $leaveWithPermissionDaysYTD = "000"; // leng 3, NNN
        $leaveWithPermissionDaysTot = "000"; // leng 4, NNN


        $statusSegments = "";

        // for now only making one but will need to change if we do more that day hospitals.
        $statusSegment = "";
        $statusSegment .= $appointment->detail->vaed_account_class; // len 2, AA or AN
        $statusSegment .= $appointment->detail->vaed_accommodation_type; // len 1, A or N
        $statusSegment .= 'X'; // len 1, A, only relevant to pregnancy
        $statusSegment .= '01'; // len 2, NN patients days MTD
        $statusSegment .= '001'; // len 3, NN patients days finanial YTD
        $statusSegment .= '0001'; // len 3, NN patients days tot
        $statusSegments .=   $statusSegment;

        //These are for subsequent days the patient stays, not relevant to day hospitals
        for ($i = 0; $i < 6; $i++) {
            $statusSegment = "";
            $statusSegment .= '  '; // len 2, AA or AN
            $statusSegment .= ' '; // len 1, A or N
            $statusSegment .= ' '; // len 1, A, only relevant to pregnancy
            $statusSegment .= '00'; // len 2, NN patients days MTD
            $statusSegment .= '000'; // len 3, NN patients days finanial YTD
            $statusSegment .= '0000'; // len 3, NN patients days tot
            $statusSegments .=  $statusSegment;
        }

        $separationDate = substr($appointment->date, -2) . substr($appointment->date, 5, 2) . substr($appointment->date, 0, 4); // len 8, DDMMYYYY
        $separationTime = substr(str_replace(':', '', $appointment->end_time), 0, 4);   // len 4, HHMM
        $separationMode = $appointment->detail->vaed_separation_mode;  // len 1, A
        $transferDestination = '    '; // len 4, NNNN
        $separationReferral = '    '; // len 4, AAAA
        $carerAvailability = ' '; // len 1, A, NA to private hospitals 
        $accountClassOnSeparation = $appointment->detail->vaed_account_class; //len 2, AA or NA or NN
        $accommodationTypeOnSeparation = $appointment->detail->vaed_accommodation_type; // len 1, A or N
        $careType = str_pad($appointment->detail->vaed_care_type, 2, ' '); //len 2, AA or NA or NN

        return strtoupper(
            $transactionType .
                $uniqueKey .
                $patientIdentifier .
                $campusCode .
                $medicareNumber .
                $medicareSuffix .
                $sex .
                $maritalStatus .
                $dateOfBirth .
                $postcode .
                $locality .
                $admissionDate .
                $admissionTime .
                $admissionType .
                $admissionSource .
                $transferSource .
                $leaveWithPermissionDaysMTD .
                $leaveWithPermissionDaysYTD .
                $leaveWithPermissionDaysTot .
                $statusSegments .
                $separationDate .
                $separationTime .
                $separationMode .
                $transferDestination .
                $separationReferral .
                $carerAvailability .
                $accountClassOnSeparation .
                $accommodationTypeOnSeparation .
                $careType
        );
    }
}
