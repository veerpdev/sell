<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enum\UserRole;
use App\Models\Organization;
use Illuminate\Http\Response;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\OrganizationCreateRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Mail\NewEmployeeEmail;
use App\Models\PreAdmissionConsent;
use App\Models\PreAdmissionSection;
use App\Models\PreAdmissionQuestion;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * [Organization] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', Organization::class);

        return response()->json(
            [
                'message' => 'All Organizations',
                'data'    => Organization::all(),
            ],
            Response::HTTP_OK
        );
    }


    /**
     * [Organization] - Show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $organization);

        return response()->json(
            [
                'message'   => 'View Organization',
                'data'      => $organization,
            ],
            Response::HTTP_OK
        );
    }


    /**
     * [Organization] - Store
     *
     * @param  \App\Http\Requests\OrganizationCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrganizationCreateRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', Organization::class);

        // Create a new user as the default admin for this org
        $i = 3;
        $code = substr($request->name, 0, $i);
        while (Organization::where('code', $code)->count() > 0) {
            $code = substr($request->name, 0, ++$i);
        }

        $firstName = $request->first_name;
        $lastName = $request->last_name;
        $username = $code . '_' . $firstName[0] . '_' . $lastName;
        $i = 0;
        while (User::whereUsername($username)->exists()) {
            $i++;
            $username = $firstName[0] . $lastName . $i;
        }

        $rawPassword = Str::random(14);

        $owner = User::create([
            ...$request->safe()->only([
                'email',
                'first_name',
                'last_name',
                'mobile_number',
            ]),
            'username'      => $username,
            'password'      => Hash::make($rawPassword),
            'role_id'       => UserRole::ORGANIZATION_MANAGER,
            'outside_hours' => true,
        ]);

        // Creates the new org
        $organization = Organization::create([
            'name'                      => $request->name,
            'code'                      => $code,
            'owner_id'                  => $owner->id,
            ...$request->validated(),
        ]);

        // Sets the owners org id to the new org
        $owner->organization_id = $organization->id;
        $owner->save();

        // Sends the welcome email to the new user
        $owner->sendEmail(new NewEmployeeEmail($owner, $rawPassword));

        // Set up the default Notification Templates for this organizations
        NotificationTemplate::CreateOrganizationNotification($organization);

        // Set up the Default pre addmision consent
        PreAdmissionConsent::create(['organization_id' => $organization->id]);

        // Set up the Default pre addmision sesions/questions
        $cnt = 0;
        $section[$cnt]["title"] = "INFECTION & INFECTION RISK";
        $question[$cnt][0]["answer_format"] = "BOOLEAN";
        $question[$cnt][0]["text"] = "Are you currently experiencing any type of infection or have you been exposed to a person that is suffering an infectious disease in the past 2 weeks, i.e. COVID-19, Cold/Flu, chickenpox, measles etc. ?";
        $question[$cnt][1]["answer_format"] = "BOOLEAN";
        $question[$cnt][1]["text"] = "Have you ever been infected or colonized with a multi-resistant organism such as MRSA, VRE, CRE/CPE?";
        $question[$cnt][2]["answer_format"] = "BOOLEAN";
        $question[$cnt][2]["text"] = "Do you have a blood-borne virus such as HIV, Hepatitis C or Hepatitis B?";
        $question[$cnt][3]["answer_format"] = "BOOLEAN";
        $question[$cnt][3]["text"] = "Have you recently returned from traveling overseas [i.e. within the past 4-6 weeks] and / or have had an overnight stay at an overseas hospital or residential care facility in the past 12 months?";
        $question[$cnt][4]["answer_format"] = "BOOLEAN";
        $question[$cnt][4]["text"] = "Have you recently or any member of your family, experienced cold or flu symptoms such as muscle pain, headaches, cough, sore throat or fever";
        $question[$cnt][5]["answer_format"] = "BOOLEAN";
        $question[$cnt][5]["text"] = "Do you have diarrhea or vomiting?";

        $section[++$cnt]["title"] = "ANAESTHETIC HISTORY & ASSESSMENT";
        $question[$cnt][0]["answer_format"] = "TEXT";
        $question[$cnt][0]["text"] = "Previous surgery? What & when";
        $question[$cnt][1]["answer_format"] = "TEXT";
        $question[$cnt][1]["text"] = "Previous anaesthetic?";
        $question[$cnt][2]["answer_format"] = "BOOLEAN";
        $question[$cnt][2]["text"] = "Has a family member had life threatening complications with anaesthetic";
        $question[$cnt][3]["answer_format"] = "BOOLEAN";
        $question[$cnt][3]["text"] = "Do you suffer from reflux?";
        $question[$cnt][4]["answer_format"] = "BOOLEAN";
        $question[$cnt][4]["text"] = "Do you have any false teeth, caps, crowns, loose or chipped teeth or other dental work";
        $question[$cnt][5]["answer_format"] = "BOOLEAN";
        $question[$cnt][5]["text"] = "Do you have any issues with your neck or jaw";

        $section[++$cnt]["title"] = "MEDICAL HISTORY AND ASSESSMENT";
        $question[$cnt][0]["answer_format"] = "BOOLEAN";
        $question[$cnt][0]["text"] = "Breathing problems such as COAD, Asthma?";
        $question[$cnt][1]["answer_format"] = "BOOLEAN";
        $question[$cnt][1]["text"] = "Do you have sleep apnoea or use a CPAP machine?";
        $question[$cnt][2]["answer_format"] = "BOOLEAN";
        $question[$cnt][2]["text"] = "Do you use home oxygen?";
        $question[$cnt][3]["answer_format"] = "BOOLEAN";
        $question[$cnt][3]["text"] = "Do you have heart trouble, angina, atrial fibrillation, chest pain, high blood pressure/hypertension, stents, ischemic heart disease, pacemaker or implanted defibrillator?";
        $question[$cnt][4]["answer_format"] = "BOOLEAN";
        $question[$cnt][4]["text"] = "Are you being investigated for chest pain?";
        $question[$cnt][5]["answer_format"] = "BOOLEAN";
        $question[$cnt][5]["text"] = "Have you had a fall in the past 3 months?";
        $question[$cnt][6]["answer_format"] = "BOOLEAN";
        $question[$cnt][6]["text"] = "Do you ever use walking aid such as frame, stick or other walking aid. Do you need a wheelchair?";
        $question[$cnt][7]["answer_format"] = "BOOLEAN";
        $question[$cnt][7]["text"] = "Have you ever had a stroke or TIA? If yes, give details";
        $question[$cnt][8]["answer_format"] = "BOOLEAN";
        $question[$cnt][8]["text"] = "Do you wear hearing aids?";
        $question[$cnt][9]["answer_format"] = "BOOLEAN";
        $question[$cnt][9]["text"] = "Do you have difficulty with speech or hearing?";
        $question[$cnt][10]["answer_format"] = "BOOLEAN";
        $question[$cnt][10]["text"] = "Are you vision impaired or, do you wear glasses continually?";
        $question[$cnt][11]["answer_format"] = "BOOLEAN";
        $question[$cnt][11]["text"] = "Do you have dementia, confusion, disorientation, delirium or a history of mental illness?";
        $question[$cnt][12]["answer_format"] = "BOOLEAN";
        $question[$cnt][12]["text"] = "Do you have epilepsy?";
        $question[$cnt][13]["answer_format"] = "BOOLEAN";
        $question[$cnt][13]["text"] = "Do you have any bleeding or clotting disorder or DVT?";
        $question[$cnt][14]["answer_format"] = "BOOLEAN";
        $question[$cnt][14]["text"] = "Do you have any skin conditions or existing wounds?";
        $question[$cnt][15]["answer_format"] = "BOOLEAN";
        $question[$cnt][15]["text"] = "Do you have lymphoedema? If yes, what limb/s are affected";
        $question[$cnt][16]["answer_format"] = "BOOLEAN";
        $question[$cnt][16]["text"] = "Do you have thyroid problems?";
        $question[$cnt][17]["answer_format"] = "BOOLEAN";
        $question[$cnt][17]["text"] = "Are you or could you be, pregnant?";
        $question[$cnt][18]["answer_format"] = "TEXT";
        $question[$cnt][18]["text"] = "Do you have any other medical conditions or Illnesses we should know about?";
        $question[$cnt][19]["answer_format"] = "TEXT";
        $question[$cnt][19]["text"] = "Do you smoke? How many per day?";
        $question[$cnt][20]["answer_format"] = "TEXT";
        $question[$cnt][20]["text"] = "If you are an ex-smoker, what year did you quit";
        $question[$cnt][21]["answer_format"] = "TEXT";
        $question[$cnt][21]["text"] = "Do you drink alcohol? How many drinks per week";
        $question[$cnt][22]["answer_format"] = "TEXT";
        $question[$cnt][22]["text"] = "Do you consume any other non prescription drugs?";
        $question[$cnt][23]["answer_format"] = "TEXT";
        $question[$cnt][23]["text"] = "If you have an advanced care directive, please list any directive related to the procedure or treatment proposed";

        $section[++$cnt]["title"] = "DIABETES";
        $question[$cnt][0]["answer_format"] = "TEXT";
        $question[$cnt][0]["text"] = "Are you diabetic? If yes, what is your blood sugar normally";
        $question[$cnt][1]["answer_format"] = "TEXT";
        $question[$cnt][1]["text"] = "If you take diabetes tablets, give details";
        $question[$cnt][2]["answer_format"] = "TEXT";
        $question[$cnt][2]["text"] = "If you have insulin, have you been given instructions on dosing prior to and post procedure";

        $section[++$cnt]["title"] = "MEDICATIONS AND ALLERGIES";
        $question[$cnt][0]["answer_format"] = "BOOLEAN";
        $question[$cnt][0]["text"] = "Are you allergic to any drugs or medications";
        $question[$cnt][1]["answer_format"] = "TEXT";
        $question[$cnt][1]["text"] = "Do you have any other allergies e.g. food or latex";
        $question[$cnt][2]["answer_format"] = "BOOLEAN";
        $question[$cnt][2]["text"] = "Do you take any medications?";

        for ($sectionIndex = 0; $sectionIndex <= $cnt; $sectionIndex++) {
            $sec = PreAdmissionSection::create(['organization_id' => $organization->id, 'title' => $section[$sectionIndex]["title"]]);
            foreach ($question[$sectionIndex] as $que) {
                PreAdmissionQuestion::create([
                    'pre_admission_section_id' => $sec->id,
                    'text' => $que['text'],
                    'answer_format' => $que['answer_format'],
                ]);
            }
        }

        return response()->json(
            [
                'message' => 'Organization created',
                'data' => $organization,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Organization] - Update
     *
     * @param  \App\Http\Requests\OrganizationRequest  $request
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(
        OrganizationUpdateRequest $request,
        Organization $organization
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $organization);
        $organization->update(
            $request->safe()->only([
                'max_clinics',
                'max_employees',
                'has_billing',
                'has_coding',
            ])
        );

        if ($file = $request->file('logo')) {
            $fileName = saveFile($file);
            $organization->logo = $fileName;
        }

        $organization->save();

        return response()->json(
            [
                'message'   => 'Organization updated',
                'data'      => $organization,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Organization] - Destroy
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $organization);

        $owner = $organization->owner;
        $owner->delete();
        $organization->delete();

        return response()->json(
            [
                'message' => 'Organization Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
