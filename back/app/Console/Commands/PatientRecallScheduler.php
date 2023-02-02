<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\PatientRecall;
use App\Models\PatientRecallSentLog;
use App\Notifications\RecallNotification;
use Illuminate\Console\Command;

class PatientRecallScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:recall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Recall message to patient';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->sendCurrentRecalls();

        $this->info('Successfully sent Recall Messages');
    }



    /**
     * Send Recall Message to Patient
     */
    private function sendCurrentRecalls()
    {
        $organizations = Organization::get();
        foreach ($organizations as $organization) {
            $template = $organization->notificationTemplates->where('type', 'recall')->first();

            $daysBefore = $template->days_before;
            $date = now()->addDays($daysBefore)->toDateString();

            $recalls = PatientRecall::where('organization_id', $organization->id)
                ->where('date_recall_due', $date)
                ->with('patient')
                ->get();

            foreach ($recalls as $recall) {
                $recall->sendNotification();

                $recall->confirmed = true;
                $recall->save();

                $patientRecallSentLog = new PatientRecallSentLog();
                $patientRecallSentLog->patient_recall_id = $recall->id;
                $patientRecallSentLog->recall_sent_at = date('Y-m-d H:i:s');
                $patientRecallSentLog->sent_by = $recall->patient->appointment_confirm_method;
                $patientRecallSentLog->save();
            }
        }
    }
}
