<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\PreAdmissionConsentRequest;
use App\Http\Requests\PreAdmissionSectionRequest;
use App\Models\PreAdmissionConsent;
use App\Models\PreAdmissionSection;
use Illuminate\Http\Response;

class PreAdmissionController extends Controller
{
    /**
     * [Pre Admission] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', PreAdmissionSection::class);

        $organizationId = auth()->user()->organization_id;

        $preAdmissionSection = PreAdmissionSection::where(
            'organization_id',
            $organizationId
        )
            ->with('questions')
            ->get();

        return response()->json(
            [
                'message' => 'Pre Admission Section List',
                'data' => $preAdmissionSection,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Store
     *
     * @param  \App\Http\Requests\PreAdmissionSectionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PreAdmissionSectionRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PreAdmissionSection::class);

        $organizationId = auth()->user()->organization_id;

        $preAdmissionSectionList = PreAdmissionSection::where(
            'organization_id',
            $organizationId
        )
            ->with('questions')
            ->get();
        foreach ($preAdmissionSectionList as $preAdmissionSection) {
            $isUpdated = false;
            for ($i = 0; $i < count($request->sections); $i++) {
                if ($preAdmissionSection->id === $request->sections[$i]['id']) {
                    $this->authorize('update', $preAdmissionSection);

                    $preAdmissionSection->update([
                        'organization_id' => $organizationId,
                        'title' => $request->sections[$i]['title'],
                        'questions' => $request->sections[$i]['questions'],
                    ]);
                    $isUpdated = true;
                    break;
                }
            }
            if (!$isUpdated) {
                $this->destroy($preAdmissionSection);
            }
        }

        foreach ($request->sections as $preAdmissionSection) {
            if (!isset($preAdmissionSection['id'])) {

                PreAdmissionSection::createSection([
                    'organization_id' => $organizationId,
                    'title' => $preAdmissionSection['title'],
                    'questions' => $preAdmissionSection['questions'],
                ]);
            }
        }

        $preAdmissionSection = PreAdmissionSection::where(
            'organization_id',
            $organizationId
        )
            ->with('questions')
            ->get();

        return response()->json(
            [
                'message' => 'Pre Admission Section List',
                'data' => $preAdmissionSection,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Create
     *
     * @param  \App\Models\PreAdmissionSection  $pre_admission_section
     * @return \Illuminate\Http\Response
     */
    public function create(
        PreAdmissionSection $preAdmissionSection
    ) {
        // Verify the user can access this function via policy
        $this->authorize('create', $preAdmissionSection);

        $organizationId = auth()->user()->organization_id;

        $preAdmissionSection = PreAdmissionSection::createSection([
            'organization_id' => $organizationId,
            'title' => $preAdmissionSection->title,
            'questions' => $preAdmissionSection->questions,
        ]);

        return response()->json(
            [
                'message' => 'Pre Admission Section updated',
                'data' => $preAdmissionSection,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Update
     *
     * @param  \App\Http\Requests\PreAdmissionSectionRequest  $request
     * @param  \App\Models\PreAdmissionSection  $pre_admission_section
     * @return \Illuminate\Http\Response
     */
    public function update(
        PreAdmissionSectionRequest $request,
        PreAdmissionSection $preAdmissionSection
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $preAdmissionSection);

        $organizationId = auth()->user()->organization_id;

        $preAdmissionSection = $preAdmissionSection->update([
            'organization_id' => $organizationId,
            'title' => $request->title,
            'questions' => $request->questions,
        ]);

        return response()->json(
            [
                'message' => 'Pre Admission Section updated',
                'data' => $preAdmissionSection,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Destroy
     *
     * @param  \App\Models\PreAdmissionSection  $pre_admission_section
     * @return \Illuminate\Http\Response
     */
    public function destroy(PreAdmissionSection $preAdmissionSection)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $preAdmissionSection);

        $preAdmissionSection->delete();

        return response()->json(
            [
                'message' => 'Pre Admission Section Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * [Pre Admission] - Update Consent
     *
     * @param  \App\Models\PreAdmissionSection  $pre_admission_section
     * @return \Illuminate\Http\Response
     */
    public function updateConsent(PreAdmissionConsentRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PreAdmissionConsent::class);

        $organizationId = auth()->user()->organization_id;

        $preAdmissionConsent = PreAdmissionConsent::where(
            'organization_id',
            $organizationId
        )->firstOrCreate();

        // Verify the user can access this function via policy
        $this->authorize('update', $preAdmissionConsent);

        $preAdmissionConsent->update([
            'organization_id' => $organizationId,
            'text' => $request->text,
        ]);

        return response()->json(
            [
                'message' => 'Pre Admission Consent updated',
                'data' => $preAdmissionConsent,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Get Consent
     *
     * @param  \App\Models\PreAdmissionSection  $pre_admission_section
     * @return \Illuminate\Http\Response
     */
    public function getConsent()
    {
        $organizationId = auth()->user()->organization_id;

        $preAdmissionConsent = PreAdmissionConsent::where(
            'organization_id',
            $organizationId
        )->firstOrCreate();

        // Verify the user can access this function via policy
        $this->authorize('view', $preAdmissionConsent);

        return response()->json(
            [
                'message' => 'Pre Admission Consent for Your Organization',
                'data' => $preAdmissionConsent,
            ],
            Response::HTTP_OK
        );
    }
}
