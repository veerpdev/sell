<?php

namespace App\Http\Controllers;

use App\Models\AutoText;
use App\Http\Requests\AutoTextIndexRequest;
use App\Http\Requests\AutoTextRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AutoTextController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AutoTextIndexRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', AutoText::class);

        $params = $request->validated();
        $autoTexts = AutoText::where('organization_id', auth()->user()->organization_id);
        foreach ($params as $column => $param) {
            
            if (!empty($param)) {
                $autoTexts = $autoTexts->where($column, '=', $param);
            }
        }

        return response()->json(
            [
                'message' => 'AutoText List',
                'data' => $autoTexts->get(),
            ],
            Response::HTTP_OK
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAutoTextRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AutoTextRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', AutoText::class);

        $autoText = AutoText::create([
            'user_id' => auth()->user()->id,
            'organization_id'   => auth()->user()->organization_id,
            ...$request->validated()
        ]);

        return response()->json(
            [
                'message' => 'New Auto Text created',
                'data' => $autoText,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAutoTextRequest  $request
     * @param  \App\Models\AutoText  $autoText
     * @return \Illuminate\Http\Response
     */
    public function update(AutoTextRequest $request, AutoText $autoText)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $autoText);

        $autoText->update([
            'user_id' => auth()->user()->id,
            'organization_id'   => auth()->user()->organization_id,
            ...$request->validated()
        ]);

        return response()->json(
            [
                'message' => 'Auto Text updated',
                'data' => $autoText,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AutoText  $autoText
     * @return \Illuminate\Http\Response
     */
    public function destroy(AutoText $autoText)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $autoText);

        $autoText->delete();

        return response()->json(
            [
                'message' => 'Auto Text Removed',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}
