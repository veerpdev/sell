<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Requests\FileRequest;
use Illuminate\Support\Facades\Storage;


class FileController extends Controller
{
    /**
     * Return a particular file for viewing
     *
     * @group Appointments
     * @param  \App\Http\Requests\FileRequest  $request
     * @return \Illuminate\Http\Response
     */

    public function show(FileRequest $request)
    {
        $folder =  "files/" . auth()->user()->organization_id;

        $path = "{$folder}/{$request->path}";
        $file = Storage::get($path);

        if (!$file) {
            // If there's no file, return a 404.
            // Likely this is because the user doesn't have access
            return response()->json(
                [
                    'message'   => 'Could not find file',
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        if (Storage::mimeType($file)  === 'pdf') {
            return response($file, Response::HTTP_OK)->header('Content-Type', 'application/pdf');
        }

        return response($file, Response::HTTP_OK)->header('Content-Type', 'image/png');
    }
}
