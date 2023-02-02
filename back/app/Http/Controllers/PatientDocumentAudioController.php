<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Response;
use App\Models\PatientDocument;
use App\Models\PatientSpecialistAudio;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PatientDocumentAudioStoreRequest;
use App\Http\Requests\PatientDocumentAudioUpdateRequest;
use App\Http\Requests\PatientDocumentAudioUploadRequest;

class PatientDocumentAudioController extends Controller
{

    private $orgNotFoundText = "Could not find user organization";

    /**
     * [Patient Document Audio] - Store
     *
     * @param  \App\Http\Requests\PatientDocumentAudioStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PatientDocumentAudioStoreRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientDocument::class);
        $this->authorize('create', PatientSpecialistAudio::class);

        $userId = auth()->user()->id;
        $data = [
            ...$request->all(),
            'document_type' => 'AUDIO',
            'created_by'    => $userId,
        ];
        $patientDocument = PatientDocument::create($data);

        if ($file = $request->file('file')) {
           $fileName = saveFile($file);
        }

        PatientSpecialistAudio::create([
            ...$request->all(),
            'patient_document_id' => $patientDocument->id,
            'file_path'           => $fileName ?? null,
            'translated_by'       => $userId,
        ]);

        return response()->json(
            [
                'message' => 'New Patient Audio Created',
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Patient Document Audio] - Update
     *
     */
    public function update(PatientDocumentAudioUpdateRequest $request, PatientSpecialistAudio $patientDocumentsAudio)
    {
        $patientDocument = $patientDocumentsAudio->patient_document;

        // Verify the user can access this function via policy
        $this->authorize('update', $patientDocument);
        $this->authorize('update', $patientDocumentsAudio);

        $data = $request->all();
        if ($file = $request->file('file')) {
            $filePath = $patientDocument->storeFile($file, FileType::PATIENT_DOCUMENT);

            if (!$filePath) {
                return response()->json(
                    [
                        'message'   => $this->orgNotFoundText,
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $data['file_path'] = $filePath;
        }

        $patientDocumentsAudio->update($data);
        $patientDocument->update($data);

        return response()->json(
            [
                'message' => 'Patient Specialist Audio Updated',
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Patient Document Audio] - Upload
     */
    public function upload(Patient $patient, PatientDocumentAudioUploadRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientDocument::class);
        $this->authorize('create', PatientSpecialistAudio::class);

        $userId = auth()->user()->id;

        $data = [
            ...$request->all(),
            'patient_id'    => $patient->id,
            'document_type' => 'AUDIO',
            'created_by'    => $userId,
        ];

        $patientDocument = PatientDocument::create($data);

        if ($file = $request->file('file')) {
            $filePath = $patientDocument->storeFile($file, FileType::PATIENT_DOCUMENT);

            if (!$filePath) {
                return response()->json(
                    [
                        'message'   => $this->orgNotFoundText,
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        PatientSpecialistAudio::create([
            ...$request->all(),
            'patient_id'          => $patient->id,
            'patient_document_id' => $patientDocument->id,
            'file_path'           => $filePath ?? null,
            'translated_by'       => $userId,
        ]);

        return response()->json(
            [
                'message' => 'Patient Audio Uploaded',
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Patient Document Audio] - Destroy
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(PatientSpecialistAudio $patientDocumentsAudio)
    {
        $patientDocument = $patientDocumentsAudio->patientDocument;

        // Verify the user can access this function via policy
        $this->authorize('delete', $patientDocument);
        $this->authorize('delete', $patientDocumentsAudio);

        $patientDocument->delete();
        $patientDocumentsAudio->delete();

        return response()->json(
            [
                'message' => 'Patient Specialist Audio Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
