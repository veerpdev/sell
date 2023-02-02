<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Models\HrmScheduleTimeslot;
use App\Http\Requests\HrmScheduleTimeslotRequest;
use App\Models\User;


class HrmScheduleTimeslotController extends Controller
{

    public function index()
    {

        $this->authorize('viewAny', HrmScheduleTimeslot::class);

        $organization = auth()->user()->organization;
        $users = User::where('organization_id', $organization->id)
            ->wherenot('role_id', UserRole::ADMIN)
            ->with(['scheduleTimeslots' =>
            function ($query) {
                $query->where('is_template', true);
            }])
            ->get();

        return response()->json(
            [
                'message' => 'Schedule templates',
                'data' => $users,
            ],
            200
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HrmScheduleTimeslotRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(HrmScheduleTimeslotRequest $request)
    {
        $this->authorize('create', HrmScheduleTimeslot::class);

        $hrmScheduleTimeslot = HrmScheduleTimeslot::create($request->validated());
        return response()->json(
            [
                'message' => 'Schedule templated created',
                'data' => $hrmScheduleTimeslot,
            ],
            200
        );
    }

    /**
     * update a resource in storage.
     *
     * @param \App\Http\Requests\HrmScheduleTimeslotRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(HrmScheduleTimeslotRequest $request)
    {
        $this->authorize('update', HrmScheduleTimeslot::class);
        $this->authorize('delete', HrmScheduleTimeslot::class);

        $timeslots = $request->timeslots;
        $deleteTimeslots = $request->deleteTimeslots;

        if (count($deleteTimeslots) > 0) {
            foreach ($deleteTimeslots as $id) {
                $hrmScheduleTimeslot = HrmScheduleTimeslot::where('id', $id)->delete();
            }
        }

        foreach ($timeslots as $slot) {
            if (array_key_exists('id', $slot)) {
                $hrmScheduleTimeslot = HrmScheduleTimeslot::where('id', $slot['id'])->first();
                $hrmScheduleTimeslot->update($slot);
            } else {
                $hrmScheduleTimeslot = HrmScheduleTimeslot::create([
                    'organization_id' => auth()->user()->organization_id,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'clinic_id' => $slot['clinic_id'],
                    'week_day' => $slot['week_day'],
                    'category' => $slot['category'],
                    'restriction' => $slot['restriction'],
                    'user_id' => $slot['user_id'],
                    'is_template' => $slot['is_template'],
                    'anesthetist_id' => $slot['anesthetist_id'],
                ]);
            }
        }

        return response()->json(
            [
                'message' => 'Timeslot updated',
                'data' => $hrmScheduleTimeslot,
            ],
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\HrmScheduleTimeslot $hrmScheduleTimeslot
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(HrmScheduleTimeslot $hrmScheduleTimeslot)
    {
        $this->authorize('delete', HrmScheduleTimeslot::class);

        $hrmScheduleTimeslot->delete();
        return response()->json(
            [
                'message' => 'Schedule timeslot deleted',
            ],
            204
        );
    }
}
