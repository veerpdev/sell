<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\AppointmentType;
use App\Models\AppointmentTimeRequirement;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentSearchAvailableController extends Controller
{

    /**
     * [Appointment] - Search Available
     *
     * @group Appointments
     * @param \App\Http\Requests\Request $request
     * @param \App\Models\Appointment $appointment
     * @urlParam clinic_id           A Clinic id.                                Example: 1
     * @urlParam date                Number of weeks on the future to search.    Example: 2015-07-02
     * @urlParam specialist_id       A Specialist user Id.                            Example: 16
     * @urlParam appointment_type_id An Appointment Type id.                     Example: 3
     * @urlParam time_requirement    A Time Requirement Id.                      Example: 4
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {
        $searchDate = Carbon::createFromFormat('d/m/Y', $request->date)->startOfWeek();
        $availableStartTimes = $this->searchAvailableSlots($request, $searchDate);

        // Search appointment in next 10 weeks if there is no free slots
        for ($i = 1; $i <= 10; $i++) {
            if (count($availableStartTimes) <= 0) {
                $availableStartTimes = $this->searchAvailableSlots($request, $searchDate);
            } else {
                break;
            }
        }


        return response()->json(
            [
                'message' => 'Available Start times',
                'data' => $availableStartTimes
            ],
            Response::HTTP_OK
        );
    }

    public function appointmentCount(Request $request)
    {
        $monthString = $request->month_string;
        $yearString = $request->year_string;

        $appointmentAvailabilities = [];
        //e.g. date : 3 appointments_availability : NONE (FULLY_BOOKED, ALMOST_FULLY_BOOKED,  AVAILABLE_APPOINTMENTS, )
        $month = Carbon::parse($monthString)->month;
        $daysInMonth = Carbon::parse($monthString)->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::parse("{$yearString}-{$month}-{$i}")->format('y-m-d');
            $day = strtoupper(Carbon::parse($date)->format('D'));
            // If no specialist working on day return NONE
            $specialistsWorking = User::where('organization_id', auth()->user()->organization_id)
                ->where('role_id', UserRole::SPECIALIST)
                ->whereHas('scheduleTimeslots', function ($query) use ($day) {
                    $query->where('week_day', $day);
                })->get();

            if ($specialistsWorking->count() > 0) {
                $appointmentAvailabilities[] = [
                    'date' => $i,
                    'appointments_availability' => 'AVAILABLE_APPOINTMENTS'
                ];

                //TODO check for free time slots, return FULLY_BOOKED if none
            } else {
                $appointmentAvailabilities[] = [
                    'date' => $i,
                    'appointments_availability' => 'NONE'
                ];
            }
        }


        return response()->json(
            [
                'message' => 'Appointment count',
                'data' => $appointmentAvailabilities
            ],
            Response::HTTP_OK
        );
    }

    private function getSpecialistforSlot($specialist, $time, $day, $searchDate, $aptType)
    {
        if (
            $specialist->canWorkAt($time, $day, $searchDate->timestamp)
            && $specialist->canAppointmentTypeAt($time, $day, $aptType)
            && !$specialist->hasAppointmentAtTime($time, $searchDate->timestamp)
        ) {

            $hrmUserBaseSchedule = $specialist->hrmUserBaseScheduleAtTimeDay($time, $day);
            return [
                'time' => date('H:i', $time),
                'specialist_name' => $specialist->full_name,
                'specialist_id' => $specialist->id,
                'clinic_id' => $hrmUserBaseSchedule->clinic_id,
                'clinic_name' => Clinic::find($hrmUserBaseSchedule->clinic_id)->name,
            ];
        }
        return false;
    }

    private function getTimeFrameParameter($time_requirement)
    {
        $organization = auth()->user()->organization;
        $timeslotLength = $organization->appointment_length;
        $startTime = Carbon::create($organization->start_time)->timestamp;
        $endTime = Carbon::create($organization->end_time)->timestamp;
        if ($time_requirement) {
            $timeRequirement = AppointmentTimeRequirement::find($time_requirement);
            $baseTime = Carbon::create($timeRequirement->base_time)->timestamp;
            if ($timeRequirement->type == 'After') {
                if ($startTime < $baseTime) {
                    $startTime = $baseTime;
                }
            } elseif ($timeRequirement->type == 'Before' && $endTime > $baseTime) {
                $endTime = $baseTime;
            }
        }
        return [
            'timeslotLength' => $timeslotLength,
            'startTime' => $startTime,
            'endTime' => $endTime
        ];
    }

    private function searchAvailableSlots(Request $request, $searchDate)
    {
        // Appointment Type
        $appointmentType = AppointmentType::where('id', $request->appointment_type_id)->first();
        //Clinic Id
        $clinicId = $request->clinic_id;

        // Search date date

        // Time Frame To Search and get org start time and end time
        $timeframeParameters = $this->getTimeFrameParameter($request->time_requirement);
        $timeslotLength = $timeframeParameters['timeslotLength'];
        $startTime = $timeframeParameters['startTime'];
        $endTime = $timeframeParameters['endTime'];;

        // Return a week long list of available appointment slots within the given parameters
        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $availableStartTimes = [];

        foreach ($days as $day) {
            $availableTimeslots = [];
            $specialists = collect([]);
            if ($request->specialist_id) {
                // Get selected User
                $selectedSpecialist = user::where('id', $request->specialist_id)
                    ->where('organization_id', auth()->user()->organization_id)
                    ->first();
                $specialists->add($selectedSpecialist);
            } else {
                // Get All specialist working on given day
                $specialists = User::where('organization_id', auth()->user()->organization_id)
                    ->where('role_id', UserRole::SPECIALIST)
                    ->whereHas('scheduleTimeslots', function ($query) use ($day, $clinicId) {
                        $query->where('week_day', $day);
                        if ($clinicId != "") {
                            $query->where('clinic_id', $clinicId);
                        }
                    })->get();
            }

            for ($time = $startTime; $time < $endTime; $time += $timeslotLength * 60) {
                foreach ($specialists as $specialist) {
                    // check specialist available for selected time slot
                    $filteredSpecialist = $this
                        ->getSpecialistforSlot($specialist, $time, $day, $searchDate, $appointmentType);
                    if (!$filteredSpecialist) {
                        continue;
                    } else {
                        array_push($availableTimeslots, $filteredSpecialist);
                        break;
                    }
                }
            }
            $availableStartTimes[] = [
                'day' => $day,
                'date' => $searchDate->format('Y-m-d'),
                'available_timeslots' => $availableTimeslots
            ];
            $searchDate = $searchDate->addDay();
        }


        $result = 0;
        foreach ($availableStartTimes as $day) {
            if (count($day['available_timeslots']) >= 1) {
                $result++;
            }
        }
        if ($result === 0) {
            return [];
        }
        return $availableStartTimes;
    }
}
