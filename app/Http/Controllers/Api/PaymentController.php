<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ApiResponse;

class PaymentController extends Controller
{
    use ApiResponse;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request, $bookingId)
    {
        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                return $this->error('Booking is not in pending status');
            }

            $result = $this->paymentService->processPayment($booking);

            return $this->success($result, 'Payment processed');
        } catch (\Exception $e) {
            return $this->error('Failed to process payment');
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            if ($payment->booking->user_id !== request()->user()->id) {
                return $this->forbidden('You can only view your own payments');
            }

            return $this->success($payment, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFound('Payment not found');
        }
    }
}
