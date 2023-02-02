<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Requests\ProfileUpdateRequest;

class UserProfileController extends Controller
{


    /**
     * [User] - User Profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json(
            [
                'message'   => 'Yhe user profile',
                'data'      => auth()->user()->makeHidden([
                    'password',
                    'created_at',
                    'updated_at',
                    'remember_token',
                    'password_changed_date',
                    'email_verified_at'
                ])
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [User] - Update User Profile
     *
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = auth()->user();

        // Verify the user can access this function via policy
        $this->authorize('updateProfile', $user);

        $user->update($request->safe()->except(['photo']));

        if ($file = $request->file('photo')) {
            $fileName = saveFile($file);
            $user->photo = $fileName;
            $user->save();
        }

        return response()->json(
            [
                'message' => 'User Profile updated',
                'data' => $user,
            ],
            Response::HTTP_OK
        );
    }
}
