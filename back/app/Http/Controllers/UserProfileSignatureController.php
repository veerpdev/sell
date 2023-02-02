<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignatureUpdateRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class UserProfileSignatureController extends Controller
{


    /**
     * [User] - Update Profile Picture
     *
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function update(SignatureUpdateRequest $request)
    {
        $params = $request->validated();
        $user = auth()->user();

        // Verify the user can access this function via policy
        $this->authorize('updateProfile', $user);


        if ($signature = $request->signature) {
            $image_parts = explode(";base64,", $signature);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            $file_name = 'user_signature_' . $user->id . '.' . $image_type;
            $user->signature =  $file_name;
            $path = 'files/'.$user->organization_id.'/';
            Storage::put($path.$file_name, $image_base64);
        }

     
        $user->education_code = $request->education_code;
        $user->sign_off = $request->sign_off;
        $user->save();

        return response()->json(
            [
                'success' => true,
                'message' => 'Signature changed successfully',
            ],
            Response::HTTP_OK
        );
    }
}
