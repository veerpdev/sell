<?php

namespace App\Http\Controllers;


use Illuminate\Http\Response;
use App\Models\DocumentHeaderFooterTemplate;
use App\Http\Requests\DocumentHeaderFooterTemplateRequest;

class DocumentHeaderFooterTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verify the user can access this function via policy
        // $this->authorize('viewAny', DocumentHeaderFooterTemplate::class);

        $organizationId = auth()->user()->organization_id;

        $templates = DocumentHeaderFooterTemplate::where(
            'organization_id',
            $organizationId
        )
            ->get();

        return response()->json(
            [
                'message' => 'Document Header/Footer Template List',
                'data' => $templates,
            ],
            Response::HTTP_OK
        );
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\DocumentHeaderFooterTemplateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentHeaderFooterTemplateRequest $request)
    {
        $action = 'create';
        $documentHeaderFooterTemplate = null;
        $organizationId = auth()->user()->organization_id;

        // Verify the user can access this function via policy
        if (!$request->id) {
            $this->authorize($action, DocumentHeaderFooterTemplate::class);

            $documentHeaderFooterTemplate = DocumentHeaderFooterTemplate::create([
                'title'           => $request->title,
                'organization_id' => $organizationId,
                'header_file'     => 'null',
                'footer_file'     => 'null',
                'user_id'         => $request->user_id ? $request->user_id : null,
            ]);
        } else {
            $action = 'update';
            $documentHeaderFooterTemplate = DocumentHeaderFooterTemplate::find($request->id);

            $this->authorize($action, $documentHeaderFooterTemplate);

            $documentHeaderFooterTemplate->title = $request->title;
            $documentHeaderFooterTemplate->save();
        }

        if ($file = $request->file('header_file')) {
            $fileName = saveFile($file);
            $documentHeaderFooterTemplate->header_file = $fileName;
            $documentHeaderFooterTemplate->save();
        }

        if ($file = $request->file('footer_file')) {
            $fileName = saveFile($file);
            $documentHeaderFooterTemplate->footer_file = $fileName;
            $documentHeaderFooterTemplate->save();
        }

        return response()->json(
            [
                'message' => 'New Document Header/Footer Template created',
                'data' => $documentHeaderFooterTemplate,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\DocumentHeaderFooterTemplateRequest  $request
     * @param  \App\Models\DocumentHeaderFooterTemplate  $documentHeaderFooterTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(
        DocumentHeaderFooterTemplateRequest $request,
        DocumentHeaderFooterTemplate $documentHeaderFooterTemplate
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $documentHeaderFooterTemplate);

        $organizationId = auth()->user()->organization_id;

        $documentHeaderFooterTemplate = $documentHeaderFooterTemplate->update([
            'organization_id' => $organizationId,
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'Document Header/Footer Template updated',
                'data' => $documentHeaderFooterTemplate,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentHeaderFooterTemplate  $documentHeaderFooterTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocumentHeaderFooterTemplate $documentHeaderFooterTemplate)
    {
        $this->authorize('delete', $documentHeaderFooterTemplate);

        $documentHeaderFooterTemplate->delete();

        return response()->json(
            [
                'message' => 'Document Header/Footer Template Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
