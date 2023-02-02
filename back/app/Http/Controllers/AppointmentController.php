<?php

namespace App\Http\Controllers;

use App\Enum\ConfirmationStatus;
use App\Http\Requests\AppointmentBulkUpdateRequest;
use App\Http\Requests\AppointmentCreateRequest;
use App\Http\Requests\AppointmentIndexRequest;
use App\Http\Requests\AppointmentUpdateRequest;
use App\Models\AppointmentType;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\Patient;
use App\Models\PatientBilling;
use App\Models\AppointmentPreAdmission;
use App\Models\AppointmentReferral;
use App\Models\Organization;
use App\Models\PatientAlsoKnownAs;
use App\Models\ScheduleItem;
use App\Models\User;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(AppointmentIndexRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', Appointment::class);

        $appointments = Appointment::where('organization_id', auth()->user()->organization_id)
            ->wherenot('confirmation_status', ConfirmationStatus::CANCELED)
            ->with('appointment_type')
            ->with('referral')
            ->with('anesthetist')
            ->with(['specialist.hrmWeeklySchedule' => function ($query) {
                $query->where('status', 'PUBLISHED');
            }])
            ->with('specialist.hrmWeeklySchedule.anesthetist')
            ->orderBy('date')
            ->orderBy('start_time');

        $params = $request->validated();
        foreach ($params as $column => $param) {

            if ($column == 'date') {
                $dateFormatted = Carbon::parse($param)->format('Y-m-d');
                $appointments = $appointments->where('date', $dateFormatted)
                    ->with([
                        'specialist.hrmWeeklySchedule' => function ($query) use ($dateFormatted) {
                            $query->where('date', $dateFormatted)->where('status', 'PUBLISHED');
                        }
                    ]);
            } elseif ($column == 'before_date') {
                $dateFormatted = Carbon::parse($param)->format('Y-m-d');
                $appointments = $appointments->where('date', '<=', $dateFormatted);
            } elseif ($column == 'after_date') {
                $dateFormatted = Carbon::parse($param)->format('Y-m-d');
                $appointments = $appointments->where('date', '>=', $dateFormatted);
            } else {
                $appointments = $appointments->where($column, '=', $param);
            }
        }
        return response()->json(
            [
                'message' => 'Appointments',
                'data' => $appointments->get(),
            ],
            Response::HTTP_OK
        );
    }


    public function show(Appointment $appointment)
    {
        // Verify the user can access this function via policy
        $this->authorize('view', $appointment);

        return response()->json(
            [
                'message' => 'Appointment List',
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\AppointmentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AppointmentCreateRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', Appointment::class);
        $this->authorize('create', AppointmentReferral::class);
        $this->authorize('create', AppointmentPreAdmission::class);

        $patient = Patient::find($request->patient_id);
        if ($patient) {
            // Verify the user can access this function via policy
            $this->authorize('update', $patient);


            $patient->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'date_of_birth' => Carbon::create($request->date_of_birth)->toDateString(),
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'email' => $request->email,
                'appointment_confirm_method' => $request->appointment_confirm_method,
                'allergies' => $request->allergies,
                'clinical_alerts' => $request->clinical_alerts,
            ]);
        } else {
            // Verify the user can access this function via policy
            $this->authorize('create', Patient::class);


            $patient = Patient::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'date_of_birth' => Carbon::create($request->date_of_birth)->toDateString(),
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'email' => $request->email,
                'appointment_confirm_method' => $request->appointment_confirm_method,
                'allergies' => $request->allergies,
                'clinical_alerts' => $request->clinical_alerts,
            ]);

            $patient->organizations()->attach(Organization::find(auth()->user()->organization_id));
        }

        if ($request->claim_sources) {
            foreach ($request->claim_sources as $claim_source) {
                PatientBilling::create([
                    'is_valid' => true,
                    'verified_at' => now(),
                    'patient_id' => $patient->id,
                    ...$claim_source,
                ]);
            }
        }

        if ($request->also_known_as) {
            foreach ($request->also_known_as as $known_as) {
                PatientAlsoKnownAs::create([
                    'patient_id'  => $patient->id,
                    ...$known_as,
                ]);
            }
        }
        $startTime = Carbon::create($request->start_time);

        $appointment = Appointment::create([
            'date' => Carbon::create($request->date)->toDateString(),
            'arrival_time' => $request->arrival_time,
            'start_time' => $request->start_time,
            'end_time' => $this->aptEndTime($request)->toTimeString(),
            'patient_id' => $patient->id,
            'organization_id' => auth()->user()->organization_id,
            'appointment_type_id' => $request->appointment_type_id,
            'clinic_id' => $request->clinic_id,
            'specialist_id' => $request->specialist_id,
            'anesthetist_id' => User::find($request->specialist_id)
                ->hrmUserBaseSchedulesTimeDay(
                    $startTime->timestamp,
                    strtoupper(Carbon::parse($request->date)->format('Y-m-d'))
                )?->anesthetist_id,
            'note' => $request->note,
            'charge_type' => $request->charge_type,
            'room_id' => $request->room_id,
            'draft_status' => false,
            'created_by' => auth()->user()->id,
            'is_wait_listed' => $request->is_wait_listed,
        ]);

        $appointmentType = $appointment->appointment_type;


        $procedures = [];
        $adminItems = [];

        if ($appointmentType->default_items) {
            foreach ($appointmentType->default_items as $item_id) {
                $item = ScheduleItem::find($item_id);

                $arr = [
                    'id' => $item_id,
                    'price' => $item->amount,
                ];

                if ($item->mbs_item_code) {
                    $procedures[] = $arr;
                    continue;
                }

                $adminItems[] = $arr;
            }
        }

        AppointmentDetail::create([
            'appointment_id' => $appointment->id,
            'procedures_undertaken' => $procedures,
            'admin_items' => $adminItems,
        ]);

        AppointmentReferral::create([
            'appointment_id'                => $appointment->id,
            'doctor_address_book_id'           => $request->doctor_address_book_id,
            'referral_date'                 =>  Carbon::create($request->referral_date)->toDateString(),
            'referral_duration'             => $request->referral_duration,
            'is_no_referral'                => false,
        ]);

        AppointmentPreAdmission::create([
            'appointment_id' => $appointment->id,
            'token' => md5($appointment->id)
        ]);

        if ($request->send_forms) {
            $appointment->sendNotification('appointment_booked');
        }

        return response()->json(
            [
                'message' => 'New Appointment created',
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\AppointmentRequest $request
     * @param \App\Models\Appointment $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AppointmentUpdateRequest $request, Appointment $appointment)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $appointment);
        $this->authorize('update', $appointment->patient);
        $this->authorize('update', $appointment->referral->first());

        $appointment->update([
            'appointment_type_id' => $request->appointment_type_id,
            'room_id' => $request->room_id,
            'note' => $request->note,
            'charge_type' => $request->charge_type,
            'start_time' => $request->start_time,
            'end_time' => $this->aptEndTime($request)->toTimeString(),
        ]);

        if (isset($request->is_wait_listed)) {
            $appointment->update(['is_wait_listed' => $request->is_wait_listed]);
        }

        if ($request->clinic_id) {
            $appointment->update(['clinic_id' => $request->clinic_id]);
        }

        if ($request->specialist_id) {
            $appointment->update(['specialist_id' => $request->specialist_id]);
        }

        if ($request->date) {
            $date = Carbon::parse($request->date)->toDateString();
            $appointment->update(['date' => $date]);
        }

        $appointment->patient()->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => Carbon::create($request->date_of_birth)->toDateString(),
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'email' => $request->email,
            'appointment_confirm_method' => $request->appointment_confirm_method,
            'clinical_alerts' => $request->clinical_alerts,
        ]);

        $patient = Patient::find($request->patient_id);

        if ($request->claim_sources) {
            foreach ($request->claim_sources as $claim_source) {
                PatientBilling::create([
                    'is_valid'    => true,
                    'verified_at' => now(),
                    'patient_id'  => $patient->id,
                    ...$claim_source,
                ]);
            }
        }

        if ($request->also_known_as) {
            foreach ($request->also_known_as as $known_as) {
                PatientAlsoKnownAs::create([
                    'patient_id'  => $patient->id,
                    ...$known_as,
                ]);
            }
        }

        $appointment->referral->update([
            'doctor_address_book_id'           => $request->doctor_address_book_id,
            'referral_date'                 =>  Carbon::create($request->referral_date)->toDateString(),
            'referral_duration'             => $request->referral_duration,
            'is_no_referral'                => false,

        ]);

        return response()->json(
            [
                'message' => 'Appointment updated',
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Confirm
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        $appointment = Appointment::find($request->id);

        // Verify the user can access this function via policy
        $this->authorize('update', $appointment);

        $appointment->confirmation_status = 'CONFIRMED';

        $appointment->save();

        return response()->json(
            [
                'message' => 'Appointment confirmed',
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Appointment wait listed
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function waitListed(Request $request)
    {
        $appointment = Appointment::find($request->id);

        // Verify the user can access this function via policy
        $this->authorize('waitListed', $appointment);

        $appointment->is_wait_listed = (bool)$request->is_wait_listed;

        $appointment->save();

        $message = 'Appointment removed from wait listed';

        if ($appointment->is_wait_listed) {
            $message = 'Appointment added to wait listed';
        }

        return response()->json(
            [
                'message' => $message,
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }

    public function bulkUpdate(AppointmentBulkUpdateRequest $request)
    {
        $request = $request->validated();
        $appointments = $request['appointments'];

        foreach ($appointments as $appointment) {
            $apt = Appointment::find($appointment['id']);
            $apt->update([
                'date' => Carbon::create($appointment['date'])->toDateString(),
                'arrival_time' => $appointment['arrival_time'],
                'start_time' => $appointment['start_time'],
                'end_time' => $appointment['end_time'],
                'clinic_id' => $appointment['clinic_id'],
                'specialist_id' => $appointment['specialist_id'],
            ]);
        }
        return response()->json("Update Successfully");
    }

    public function aptEndTime(Request $request)
    {
        $startTime = Carbon::create($request->start_time);
        $organization = User::find($request->specialist_id)->organization()->first();
        $appointmentType = AppointmentType::where("id", $request->appointment_type_id)->first();
        return Carbon::create($startTime)
            ->addMinutes($organization->appointment_length * $appointmentType->AppointmentLengthAsNumber);
    }

    public function resendMessage(Request $request, Appointment $appointment)
    {
        $appointment->sendNotification('appointment_confirmation');
        return response()->json(
            [
                'message' => 'Resent Message Successfully',
                'data' => $appointment,
            ],
            Response::HTTP_OK
        );
    }
}
