<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutgoingMessageLogIndexRequest;
use App\Models\OutgoingMessageLog;
use Illuminate\Http\Response;

class OutgoingMessageLogController extends Controller
{
    public function index(OutgoingMessageLogIndexRequest $request)
    {
        $params = $request->validated();
        $outgoingMessageLogs = OutgoingMessageLog::where('organization_id', auth()->user()->organization_id);

        foreach ($params as $column => $param) {
            if (!empty($param)) {
                $outgoingMessageLogs = $outgoingMessageLogs->where($column, '=', $param);
            }
        }

        $outgoingMessageLogs = $outgoingMessageLogs->with(['patient' => function ($query) {
            $query->select('id', 'title', 'first_name', 'last_name', 'date_of_birth');
        }]);

        $outgoingMessageLogs = $outgoingMessageLogs->with(['document' => function ($query) {
            $query->select('id', 'file_type', 'file_path');
        }]);

        return response()->json(
            [
                'message' => 'Outgoing message log',
                'data' => $outgoingMessageLogs->get(),
                'param' => $params,
            ],
            Response::HTTP_OK
        );
    }
}
