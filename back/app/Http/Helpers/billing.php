<?php

if (!function_exists('generateInvoiceNumber')) {
    function generateInvoiceNumber($organization, $appointment, $number = null)
    {
        $code = strtoupper($organization->code);
        $appointmentNumber = sprintf('%05d', $appointment->id);
        $digits = sprintf('%07d', $organization->id . $appointmentNumber);

        return $code . $digits . $number;
    }
}
