<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserRequest;
use App\Mail\NewEmployeeEmail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /*
     * [User] - List
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(UserIndexRequest $request)
    {
        $params = $request->validated();
        // Verify the user can access this function via policy
        $this->authorize('viewAny', [User::class, auth()->user()->organization_id]);

        $organization = auth()->user()->organization;
        $users = User::where(
            'organization_id',
            $organization->id
        )
            ->wherenot('role_id', UserRole::ADMIN)
            ->with(['hrmWeeklySchedule' => function ($query) {
                $query->where('is_template', '1');
            }])
            ->with('hrmWeeklySchedule.anesthetist')
            ->with('scheduleTimeslots.anesthetist')
            ->with('specialistClinicRelations');

        foreach ($params as $column => $param) {
            if (!empty($param) && $column !== "date") {
                $users = $users->where($column, '=', $param);
            }
        }

        $users = $users->get();
        foreach ($users as $user) {
            $user->hrm_work_schedule = $user->generateWorkSchedule(
                array_key_exists('date', $params) ? $params['date'] : null
            );
        }

        return response()->json(
            [
                'message' => 'Employee List',
                'data' => $users,
            ],
            Response::HTTP_OK
        );
    }


    /*
     * Change avatar path to url
     */

    protected function withBaseUrl($userList)
    {
        $baseUrl = url('/');

        $userList = $userList->toArray();

        foreach ($userList as $key => $user) {
            if (substr($user['photo'], 0, 1) == '/') {
                $userList[$key]['photo'] = $baseUrl . $user['photo'];
            }
        }

        return $userList;
    }


    /*
     * [Employee] - Destroy
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */

    public function destroy(User $user)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(
            [
                'message' => 'Employee Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }

    /*
     * [Employee] - Store
     *
     * @param \App\Http\Requests\UserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */

    public function store(UserRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', [User::class, auth()->user()->organization_id, $request->role_id]);


        if (auth()->user()->organization->is_max_users) {
            return response()->json(
                [
                    'message' => 'Sorry, organization is at max users',
                ],
                422
            );
        }

        $firstName = $request->first_name;
        $lastName = $request->last_name;
        $username = auth()->user()->organization->code . '_' . $firstName[0] . '_' . $lastName;
        $i = 0;
        while (User::whereUsername($username)->exists()) {
            $i++;
            $username = $firstName[0] . $lastName . $i;
        }

        $rawPassword = Str::random(14);

        //Create New Employee
        $user = User::create([
            'organization_id' => auth()->user()->organization_id,
            'username' => $username,
            'password' => Hash::make($rawPassword),
            'password_changed_date' => date('Y-m-d H:i:s'),
            ...$request->validated()
        ]);

        $this->update($request, $user);

        $user->sendEmail(new NewEmployeeEmail($user, $rawPassword));

        return response()->json(
            [
                'message' => 'User Created',
                'data' => User::find($user->id)
            ],
            Response::HTTP_OK
        );
    }

    /*
     * [Employee] - Update
     *
     * @param \App\Http\Requests\UserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */

    public function update(UserRequest $request, User $user)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $user);

        $user = $user->update([
            ...$request->validated(),
            'schedule_timeslots' => $request->schedule_timeslots,
            'specialist_clinic_relations' => $request->specialist_clinic_relations,
        ]);
        return response()->json(
            [
                'message' => 'User updated',
                'data' => $user,
            ],
            Response::HTTP_OK
        );
    }
}
