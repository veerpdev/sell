<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\AppointmentType;
use App\Http\Requests\AppointmentTypeRequest;

class AppointmentTypeController extends Controller
{
    /**
     * [Appointment Type] - List
     *
     * @group Appointments
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', AppointmentType::class);

        $organizationId = auth()->user()->organization_id;

        $appointmentTypes = AppointmentType::where(
            'organization_id',
            $organizationId
        );

        if ($request->has('status')) {
            $appointmentTypes->where('status', $request->status);
        }

        $appointmentTypes = $appointmentTypes->get();

        return response()->json(
            [
                'message' => 'Appointment Type List',
                'data' => $appointmentTypes,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Appointment Type] - Store
     *
     * @group Appointments
     * @param  \App\Http\Requests\AppointmentTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AppointmentTypeRequest $request)
    {
        // Verify the user can access this function via policy
        // $this->authorize('create', AppointmentType::class);

        $organizationId = auth()->user()->organization_id;

        $appointmentType = AppointmentType::create([
            'organization_id' => $organizationId,
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'New Appointment Type created',
                'data' => $appointmentType,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Appointment Type] - Update
     *
     * @group Appointments
     * @param  \App\Http\Requests\AppointmentTypeRequest  $request
     * @param  \App\Models\AppointmentType  $appointmentType
     * @return \Illuminate\Http\Response
     */
    public function update(
        AppointmentTypeRequest $request,
        AppointmentType $appointmentType
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $appointmentType);
        $a = $appointmentType;
        $organizationId = auth()->user()->organization_id;

        $appointmentType->update([
            'organization_id' => $organizationId,
            'report_template' => $request->report_template,
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'Appointment Type updated',
                'data' => $appointmentType,
                'request' => $request,
                'request_validate' => $request->validated(),
                'appointmentType' => $a,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Appointment Type] - Destroy
     *
     * @group Appointments
     * @param  \App\Models\AppointmentType  $appointmentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(AppointmentType $appointmentType)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $appointmentType);

        $appointmentType->delete();

        return response()->json(
            [
                'message' => 'Appointment Type Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
