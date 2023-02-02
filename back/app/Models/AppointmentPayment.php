<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Mail\Notification;
use App\Mail\PaymentConfirmationEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'confirmed_by',
        'amount',
        'payment_type',
        'is_deposit',
        'is_send_receipt',
        'invoice_number',
        'authorized_by',
    ];

    protected $appends = [
        'confirmed_user_name',
        'authorizing_user_name',
        'full_invoice_number',
    ];

    public function getConfirmedUserNameAttribute()
    {
        return $this->confirmed_user->first_name . ' ' . $this->confirmed_user->last_name;
    }

    public function getAuthorizingUserNameAttribute()
    {
        if (!$this->authorizing_user) {
            return null;
        }

        return $this->authorizing_user->first_name . ' ' . $this->authorizing_user->last_name;
    }

    public function getFullInvoiceNumberAttribute()
    {
        return generateInvoiceNumber($this->appointment->organization, $this->appointment, $this->invoice_number);
    }

    /**
     * Return Appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Return Confirmed User
     */
    public function confirmed_user()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Return Authorizing User
     */
    public function authorizing_user()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * translate template
     */
    public function translate($template)
    {
        $appointment = $this->appointment;
        $patient = $appointment->patient();
        $clinic = $appointment->clinic;

        $words = [
            '[patient]'                     => $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name,
            '[amount]'                      => $this->amount,
            '[clinic_name]'                 => $clinic->name,
            '[total_amount]'                => '',
            '[amount_paid]'                 => '',
            '[amount_outstanding]'          => '',
            '[user_who_took_the_payment]'   => '',
        ];


        $translated = $template;

        foreach ($words as $key => $word) {
            $translated = str_replace($key, $word, $translated);
        }

        return $translated;
    }

    public function generateInvoice()
    {
        $data = $this->appointment->invoiceData();

        $totalPaid = $this->appointment->payments()
            ->where('id', '<>', $this->id)
            ->where('created_at', '<', $this->created_at)
            ->sum('amount');


        $payments = $this->appointment->payments()
            ->where('id', '<>', $this->id)
            ->where('created_at', '<', $this->created_at)
            ->orWhere('id', $this->id)
            ->get();


        $data = [
            'payment'             => $this,
            'total_paid'          => $totalPaid,
            'full_invoice_number' => $this->full_invoice_number,
            'payments'            => $payments,
            ...$data,
        ];

        foreach ($data['payments'] as &$payment) {
            if ($payment->id === $this->id) {
                $payment->current = true;
            } else {
                $payment->current = false;
            }
        }

        return PDF::loadView('pdfs/appointmentPaymentInvoice', $data);
    }

    public function sendInvoice($reissue = false)
    {
        $this->appointment->patient->sendEmail(new PaymentConfirmationEmail($this, $reissue));
    }
}
