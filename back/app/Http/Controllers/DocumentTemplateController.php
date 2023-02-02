<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTemplateIndexRequest;
use Illuminate\Http\Response;
use App\Models\DocumentTemplate;
use App\Http\Requests\DocumentTemplateRequest;

class DocumentTemplateController extends Controller
{
    /**
     * [Document Template] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DocumentTemplateIndexRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', DocumentTemplate::class);

        $data = $request->validated();

        $documentTemplate = DocumentTemplate::where('organization_id', auth()->user()->organization_id)
            ->when(isset($data['type']), function ($query) use ($data) {
                $type = str_replace('-', '_', strtoupper($data['type']));
                $query->whereType($type);
            })
            ->with('sections')
            ->get();

        return response()->json(
            [
                'message' => 'Document Template List',
                'data' => $documentTemplate,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Document Template] - Store
     *
     * @param  \App\Http\Requests\DocumentTemplateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentTemplateRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', DocumentTemplate::class);

        $documentTemplate = DocumentTemplate::createTemplate([
            'organization_id' => auth()->user()->organization_id,
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'New Document Template created',
                'data' => $documentTemplate,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * [Document Template] - Update
     *
     * @param  \App\Http\Requests\DocumentTemplateRequest  $request
     * @param  \App\Models\DocumentTemplate  $documentTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentTemplateRequest $request, DocumentTemplate $documentTemplate)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $documentTemplate);

        $documentTemplate = $documentTemplate->update([
            ...$request->validated(),
        ]);

        return response()->json(
            [
                'message' => 'Document Template updated',
                'data' => $documentTemplate,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Document Template] - Destroy
     *
     * @param  \App\Models\DocumentTemplate  $documentTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocumentTemplate $documentTemplate)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $documentTemplate);

        $documentTemplate->delete();

        return response()->json(
            [
                'message' => 'Document Template Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
