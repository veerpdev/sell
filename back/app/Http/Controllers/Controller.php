<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check conflict
     */
    protected function timeContainsSlot(
        $startTime,
        $endTime,
        $slotStartTime,
        $slotEndTime
    ) {
        if ($startTime <= $slotStartTime && $endTime >= $slotEndTime) {
            return true;
        }

        return false;
    }
}
