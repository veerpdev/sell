<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentPaymentRequest;
use App\Mail\PaymentConfirmationEmail;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use Illuminate\Http\Response;
use App\Models\ScheduleItem;
use App\Models\User;

class PaymentController extends Controller
{
    /**
     * [Payment] - List
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verify the user can access this function via policy
        $this->authorize('viewAny', Appointment::class);

        $paymentList = Appointment::where('organization_id', auth()->user()->organization_id)
            ->whereIn('confirmation_status', ['PENDING', 'CONFIRMED'])
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date')
            ->orderBy('start_time')
            ->with('payments')
            ->get()
            ->toArray();

        return response()->json(
            [
                'message' => 'Payment List',
                'data'    => $paymentList,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Payment] - Show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        // Verify the user can access this function via policy
        $this->authorize('view', [Appointment::class, $appointment]);
        $this->authorize('viewAny', AppointmentPayment::class);

        $user = auth()->user();
        $organizationId = $user->organization->id;

        $charges = [
            'procedures'  => [],
            'extra_items' => [],
            'admin_items' => [],
        ];

        if ($appointment->detail->procedures_undertaken) {
            foreach ($appointment->detail->procedures_undertaken as $procedure) {
                $scheduleItem = ScheduleItem::whereId($procedure['id'])
                    ->whereOrganizationId($organizationId)
                    ->with('schedule_fees')
                    ->first()
                    ->toArray();

                $charges['procedures'][] = [
                    ...$scheduleItem,
                    'schedule_fees' => $scheduleItem['schedule_fees'],
                    'price'         => $procedure['price'],
                    'authorized_by' => isset(
                        $procedure['authorized_by']
                    )
                        ? User::find($procedure['authorized_by'])
                        : null,
                    'deleted_by' => isset(
                        $procedure['deleted_by']
                    )
                        ? User::find($procedure['deleted_by'])
                        : null,
                ];
            }
        }

        if ($appointment->detail->extra_items_used) {
            foreach ($appointment->detail->extra_items_used as $extra_item) {
                $scheduleItem = ScheduleItem::whereId($extra_item['id'])
                    ->whereOrganizationId($organizationId)
                    ->with('schedule_fees')
                    ->first()
                    ->toArray();

                $charges['extra_items'][] = [
                    ...$scheduleItem,
                    'schedule_fees' => $scheduleItem['schedule_fees'],
                    'price'         => $extra_item['price'],
                    'authorized_by' => isset(
                        $extra_item['authorized_by']
                    )
                        ? User::find($extra_item['authorized_by'])
                        : null,
                    'deleted_by' => isset(
                        $extra_item['deleted_by']
                    )
                        ? User::find($extra_item['deleted_by'])
                        : null,
                ];
            }
        }

        if ($appointment->detail->admin_items) {
            foreach ($appointment->detail->admin_items as $admin_item) {
                $scheduleItem = ScheduleItem::whereId($admin_item['id'])
                    ->whereOrganizationId($organizationId)
                    ->with('schedule_fees')
                    ->first()
                    ->toArray();

                $charges['admin_items'][] = [
                    ...$scheduleItem,
                    'schedule_fees' => $scheduleItem['schedule_fees'],
                    'price'         => $admin_item['price'],
                    'authorized_by' => isset(
                        $admin_item['authorized_by']
                    )
                        ? User::find($admin_item['authorized_by'])
                        : null,
                    'deleted_by' => isset(
                        $admin_item['deleted_by']
                    )
                        ? User::find($admin_item['deleted_by'])
                        : null,
                ];
            }
        }

        return response()->json(
            [
                'message' => 'Payment Detail Info',
                'data' =>  [
                    'appointment'   => $appointment,
                    'organization'  => $appointment->organization,
                    'charges'       => $charges,
                    'payment_list'  => $appointment->payments,
                    'paid_amount'   => $appointment->payments()->sum('amount'),
                ]
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Payment] - Store
     *
     * @param  \App\Http\Requests\AppointmentPaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AppointmentPaymentRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', AppointmentPayment::class);

        $data = $request->validated();
        $data['amount'] = $data['amount'] * 100;

        if ($data['amount'] < 0 && !isset($data['authorized_by'])) {
            return response()->json(
                [
                    'message' => 'Authorized by is required for refunds'
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $lastPayment = AppointmentPayment::latest('created_at')->first();

        if (!$lastPayment) {
            $invoiceNumber = 1;
        } else {
            $invoiceNumber = $lastPayment->invoice_number + 1;
        }

        $payment = AppointmentPayment::create([
            ...$data,
            'invoice_number' => $invoiceNumber,
            'confirmed_by' => auth()->user()->id,
        ]);

        if ($payment->is_send_receipt) {
            $payment->sendInvoice();
        }

        return response()->json(
            [
                'message' => 'Appointment payment confirmed'
            ],
            Response::HTTP_CREATED
        );
    }
}
