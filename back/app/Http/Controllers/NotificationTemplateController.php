<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\NotificationTemplate;
use App\Http\Requests\NotificationTemplateRequest;

class NotificationTemplateController extends Controller
{
    /**
     * [Notification Template] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', NotificationTemplate::class);

        $organizationId = auth()->user()->organization_id;

        $notificationTemplates = NotificationTemplate::where(
            'organization_id',
            $organizationId
        )->get();

        return response()->json(
            [
                'message' => 'Notification Template List',
                'data' => $notificationTemplates,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Notification Template] - Store
     *
     * @param  \App\Http\Requests\NotificationTemplateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NotificationTemplateRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', NotificationTemplate::class);

        $organizationId = auth()->user()->organization_id;

        $notificationTemplate = NotificationTemplate::create([
            'organization_id' => $organizationId,
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'New Notification Template created',
                'data' => $notificationTemplate,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Notification Template] - Update
     *
     * @param  \App\Http\Requests\NotificationTemplateRequest  $request
     * @param  \App\Models\NotificationTemplate  $notificationTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(
        NotificationTemplateRequest $request,
        NotificationTemplate $notificationTemplate
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $notificationTemplate);

        $notificationTemplate->update($request->validated());

        return response()->json(
            [
                'message' => 'Notification Template updated',
                'data' => $notificationTemplate,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Notification Template] - Destroy
     *
     * @param  \App\Models\NotificationTemplate  $notificationTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(NotificationTemplate $notificationTemplate)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $notificationTemplate);

        $notificationTemplate->delete();

        return response()->json(
            [
                'message' => 'Notification Template Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
