<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Response;
use App\Http\Requests\CodingReportRequest;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CodingReportController extends Controller
{

    /**
     * [CodingReport] - Store
     *
     * @param  \App\Http\Requests\CodingReportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CodingReportRequest $request)
    {
        // Verify the user can access this function via policy

        $formattedFromDate = Carbon::parse($request->from_date)->format('d/m/Y');
        $formattedToDate = Carbon::parse($request->to_date)->format('d/m/Y');
        $content = "{$request->type}-{$formattedFromDate}-{$formattedToDate}\n";
        $content .= "Report Date: {$formattedFromDate}-{$formattedToDate}\n";
        $content .= "Report Type: {$request->type}\n";
        $fileName = "{$request->type}-" .
            Carbon::parse($request->from_date)->format('Y_m_d') . "-" .
            Carbon::parse($request->to_date)->format('Y_m_d');
        $filePath = "files/" . auth()->user()->organization_id . "/coding_reports//";
        Storage::disk('local')->put($filePath . $fileName, $content);
        return response()->download(storage_path("app/" . $filePath . $fileName));
    }
}
