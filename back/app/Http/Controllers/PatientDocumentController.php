<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use App\Models\Patient;
use App\Enum\DocumentType;
use App\Mail\DocumentEmail;
use App\Models\Appointment;
use App\Enum\DocumentOrigin;
use Illuminate\Http\Response;
use App\Models\DocumentSection;
use App\Models\PatientDocument;
use App\Models\DoctorAddressBook;
use App\Enum\OutMessageSendMethod;
use App\Enum\OutMessageSendStatus;
use App\Models\OutgoingMessageLog;
use Illuminate\Support\Facades\Mail;
use App\Models\SpecialistClinicRelation;
use App\Http\Requests\DocumentIndexRequest;
use App\Models\DocumentHeaderFooterTemplate;
use App\Http\Requests\PatientDocumentRequest;
use App\Http\Requests\PatientDocumentSaveRequest;
use App\Http\Requests\PatientDocumentPreviewRequest;
use App\Http\Requests\PatientDocumentEmailSendRequest;
use App\Models\Clinic;
use App\Models\ScheduleItem;

class PatientDocumentController extends Controller
{
    /**
     * Retrieves all documents without giver filters
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DocumentIndexRequest $request)
    {

        $params = $request->validated();
        $documents = PatientDocument::where('organization_id', auth()->user()->organization_id);

        foreach ($params as $column => $param) {

            if ($column == 'is_missing_information' && $param == 1) {
                $documents = $documents
                    ->whereNull('patient_id')
                    ->orWhereNull('specialist_id')
                    ->orWhereNull('appointment_id');
            } elseif (!empty($param)) {
                if ($param == 'before_date') {
                    $documents = $documents->where('created_at', '<=', $param);
                } elseif ($param == 'after_date') {
                    $documents = $documents->where('created_at', '>=', $param);
                } else {
                    $documents = $documents->where($column, $param);
                }
            }
        }

        $documents = $documents->orderBy('created_at', 'desc');

        return response()->json(
            [
                'message' => 'Document List',
                'data'    => $documents->get(),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Patient Document] - Store
     *
     * @param  \App\Http\Requests\PatientRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PatientDocumentRequest $request, Patient $patient)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientDocument::class);

        $specialistId = $request->specialist_id;
        if (!$request->specialist_id && $request->appointment_id) {
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $specialistId = $appointment->specialist_id;
            }
        }

        $patientDocument = PatientDocument::create([
            ...$request->validated(),
            'patient_id'        => $patient->id,
            'created_by'        => auth()->user()->id,
            'is_updatable'      => false,
            'origin'            => 'UPLOADED',
            'organization_id'   => auth()->user()->organization->id,
            'specialist_id'     => $specialistId,
        ]);

        if ($file = $request->file('file')) {
            $filePath = saveFile($file);
            $patientDocument->file_type = strtoupper($file->extension());
            $patientDocument->file_path = $filePath;
            $patientDocument->save();
        }

        return response()->json(
            [
                'message' => 'Patient Document Created',
                'data'    => $patientDocument
            ],
            Response::HTTP_CREATED
        );
    }

    public function email(PatientDocumentEmailSendRequest $request)
    {

        $params = $request->validated();
        $patientDocument = PatientDocument::find($params['document_id']);
        $documentPath = $patientDocument->file_path;

        $specialist = $patientDocument->specialist;
        $appointment = $patientDocument->appointment;
        $patient = $patientDocument->patient;
        $sendingProviderNumber = SpecialistClinicRelation::where('specialist_id', $specialist->id)
            ->where('clinic_id', $appointment->clinic_id)
            ->first();

        $folder = getUserOrganizationFilePath();

        $documentPath = "{$folder}/{$documentPath}";

        foreach ($params['to_user_emails'] as $email) {
            Mail::to($email)->send(new DocumentEmail(auth()->user()->organization->name, $documentPath));
            $receivingDoctor = DoctorAddressBook::where('practice_email', '=', $email)->first();

            $receivingDoctor && OutgoingMessageLog::create([
                'send_method'                   => OutMessageSendMethod::EMAIL,
                'send_status'                   => OutMessageSendStatus::SENT,
                'document_id'                   => $patientDocument->id,
                'sending_doctor_name'           => $specialist->full_name,
                'sending_doctor_provider'       => $sendingProviderNumber->provider_number,
                'receiving_doctor_name'         => $receivingDoctor->full_name,
                'receiving_doctor_provider'     => $receivingDoctor->provider_no,
                'organization_id'               => $patientDocument->organization_id,
                'patient_id'                    => $patient->id,
                'sending_doctor_user'           => $specialist->id,
                'sending_user'                  => auth()->user()->id,
                'message_contents'              => '',
            ]);
        }

        return response()->json(
            [
                'message' => 'Patient Document Created',

            ],
            Response::HTTP_CREATED
        );
    }

    public function preview(PatientDocumentPreviewRequest $request, Patient $patient)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientDocument::class);

        $documentSections = [];
        foreach ($request->report_data as $data) {
            $section = DocumentSection::find($data['sectionId']);

            $documentSections[] = [
                'title'    => $section->title,
                'text'     => $data['free_text_default'],
            ];
        }

        $appointment = Appointment::find($request->appointment_id);
        $clinic = Clinic::find($appointment->clinic_id);

        $specialistClinic = SpecialistClinicRelation::where('specialist_id', '=', auth()->user()->id)
            ->where('clinic_id', '=', $clinic->id)
            ->first();

        $headerFooter = DocumentHeaderFooterTemplate::find($request->header_footer_id);

        if (!$headerFooter) {
            $headerFooter = DocumentHeaderFooterTemplate::where('is_organization_default', '=', 1)->first();
        }

        $user = auth()->user();

        $headerImage = $headerFooter ? "files/{$user->organization_id}/{$headerFooter->header_file}" : null;
        $footerImage = $headerFooter ? "files/{$user->organization_id}/{$headerFooter->footer_file}" : null;

        $isReport = $request->document_type === DocumentType::REPORT->value;

        $proceduresUndertaken = null;
        $extraItemsUsed = null;

        if ($isReport) {
            $scheduleItems = ScheduleItem::whereOrganizationId(auth()->user()->organization_id)->get()->toArray();

            $proceduresUndertaken = [];
            foreach ($request->procedures_undertaken as $procedure) {
                $itemIndex = array_search($procedure['id'], array_column($scheduleItems, 'id'));
                $item = $scheduleItems[$itemIndex];

                $proceduresUndertaken[] = [
                    'name' => $item['label_name'],
                    ...$procedure,
                ];
            }

            $extraItemsUsed = [];
            foreach ($request->extra_items_used as $extraItem) {
                $itemIndex = array_search($extraItem['id'], array_column($scheduleItems, 'id'));
                $item = $scheduleItems[$itemIndex];

                $extraItemsUsed[] = [
                    'name' => $item['label_name'],
                    ...$extraItem,
                ];
            }
        }

        $pdfData = [
            'title'                => $request->title,
            'patientName'          => $patient->full_name,
            'patientDob'           => Carbon::create($patient->date_of_birth)->format('d F, Y'),
            'doctorAddressBook'    => $request->doctor_address_book,
            'date'                 => date('d F, Y'),
            'documentSections'     => $documentSections,
            'signatureImage'       => 'images/' . $user->organization_id . '/' . $user->signature,
            'fullName'             => $user->full_name,
            'signOff'              => $user->sign_off,
            'providerNumber'       => $specialistClinic->provider_number,
            'organization'         => auth()->user()->organization,
            'clinic'               => $clinic,
            'headerImage'          => $headerImage,
            'footerImage'          => $footerImage,
            'documentType'         => $request->document_type,
            'proceduresUndertaken' => $proceduresUndertaken,
            'extraItemsUsed'       => $extraItemsUsed,
            'icd10Code'            => $isReport ? $request->icd_10_code : null,
        ];

        $pdf = PDF::loadView('pdfs/patientDocument', $pdfData);
        $fileName = saveFile($pdf, true);

        return response()->json(
            [
                'message' => 'New Patient Report Created',
                'data'    => $fileName,
            ],
            Response::HTTP_CREATED
        );
    }

    public function save(PatientDocumentSaveRequest $request, Patient $patient)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientDocument::class);

        $patientDocument = PatientDocument::create([
            'patient_id'        => $patient->id,
            'appointment_id'    => $request->appointment_id,
            'specialist_id'     => $request->specialist_id,
            'document_name'     => $request->document_name,
            'document_type'     => $request->document_type,
            'file_type'         => 'PDF',
            'origin'            => DocumentOrigin::CREATED,
            'created_by'        => auth()->user()->id,
            'is_updatable'      => true,
            'organization_id'   => auth()->user()->organization_id,
        ]);

        $appointment = Appointment::find($request->appointment_id);

        if ($request->document_type === DocumentType::REPORT->value) {
            $appointmentDetail = $appointment->detail;
            $appointmentDetail->procedures_undertaken = $request->procedures_undertaken;
            $appointmentDetail->extra_items_used = $request->extra_items_used;
            $appointmentDetail->admin_items = $request->admin_items_used;
            $appointmentDetail->diagnosis_codes = $request->icd_10_code;
            $appointmentDetail->save();
        }


        $patientDocument->file_path = $request->file_name;
        $patientDocument->save();

        $referral = $appointment->referral;
        $doctor = $referral ? $referral->doctor_address_book : null;

        if ($request->should_send && $referral && $doctor) {
            $sendVia = $doctor->preferred_communication_method;

            $documentPath = $patientDocument->file_path;

            $specialist = $patientDocument->specialist;
            $appointment = $patientDocument->appointment;
            $sendingSpecialist = SpecialistClinicRelation::where('specialist_id', $specialist->id)
                ->where('clinic_id', $appointment->clinic_id)
                ->first();


            $folder = getUserOrganizationFilePath();

            $documentPath = "{$folder}/{$documentPath}";

            $organizationName = auth()->user()->organization->name;

            if ($sendVia === OutMessageSendMethod::EMAIL) {
                $email = $doctor->practice_email;
                Mail::to($email)->send(new DocumentEmail($organizationName, $documentPath));
            }

            $data = [
                'document_id'               => $patientDocument->id,
                'sending_doctor_name'       => $specialist->full_name,
                'sending_doctor_provider'   => $sendingSpecialist->provider_number,
                'receiving_doctor_name'     => $doctor->full_name,
                'receiving_doctor_provider' => $doctor->provider_no,
                'organization_id'           => $patientDocument->organization_id,
                'patient_id'                => $patient->id,
                'sending_doctor_user'       => $specialist->id,
                'sending_user'              => auth()->user()->id,
                'message_contents'          => '',
            ];

            OutgoingMessageLog::create([
                'send_method' => $sendVia,
                'send_status' => $sendVia === OutMessageSendMethod::EMAIL ? OutMessageSendStatus::SENT : OutMessageSendStatus::PENDING,
                ...$data,
            ]);
        }

        return response()->json(
            [
                'message' => 'New Patient Report Created',
                'data'    => $patientDocument->id,
            ],
            Response::HTTP_CREATED
        );
    }
}
