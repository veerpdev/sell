<?php

namespace App\Console\Commands;


use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\SpecialistClinicRelation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aranyasen\HL7\Message;
use Carbon\Carbon;

class HealthlinkCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthlink:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'checks the healthlink server for incoming communication';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $newMessages =  Storage::disk('healthlink')->files('HL7_in');

        foreach ($newMessages as $message) {
            $this->processHl7Message($message);
        }
    }

    private function processHl7Message($message)
    {
        $data = $this->parseMessageData($message);
        $specialistId = $this->getReceivingSpecialistUserId($data);
        $organizationId = 1;

        if ($specialistId) {
            $patientId = $this->findPatientId($data);
            PatientDocument::create([
                'organization_id'   => $organizationId,
                'patient_id'        => $patientId,
                'specialist_id'     => $specialistId,
                'document_name'     => 'TBD',
                'document_type'     => 'OTHER',
                'file_type'         => 'HTML',
                'document_body'     => $data['document_contents'],
                'origin'            => 'RECEIVED',
                'is_seen'           => 0,
                'created_by'        => 0,
            ]);
            Storage::disk('healthlink')->delete($message);
        } else {
            echo 'NO SPECIALIST FOUND';
        }
    }

    private function parseMessageData($message)
    {
        $hl7messageRaw = Storage::disk('healthlink')->get($message);
        $msg = new Message($hl7messageRaw);
        $msh = $msg->getSegmentsByName("MSH")[0];
        $messageType = $msh->getField(9)[0];
        if ($messageType == "REF") {
            return parseHeathLinkHL7RefMessage($msg);
        } else {
            return  parseHeathLinkHL7OruMessage($msg);
        }
    }

    private function findPatientId($data)
    {
        $patient = Patient::where('first_name', $data['pid']['patient_first_name'])
            ->where('last_name', $data['pid']['patient_last_name'])
            ->where('date_of_birth', Carbon::parse($data['pid']['patient_dob'])->format('Y-m-d'));
        return $patient->count() == 1 ?  $patient->first()->id : null;
    }

    private function getReceivingSpecialistUserId($data)
    {
        foreach ($data['prds'] as $value) {
            if ($value['provider_role'] == 'RT') {
                $providerNum = SpecialistClinicRelation::where(
                    'provider_number',
                    $value['provider_number']
                )->first();
                $specialist = $providerNum ? User::where('id', $providerNum->specialist_id)->first() : null;
                return $specialist->count() == 1 ?  $specialist->first()->id : null;
            }
        }
        return null;
    }
}
