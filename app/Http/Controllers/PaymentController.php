<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\CpdPaymentGateway;
use App\Services\MoodleClient;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout(Payment $payment)
    {
        $payment->load('enrolment.session.course', 'user');

        // If already paid, go straight to the player
        if ($payment->status === 'success') {
            return $this->redirectToPlayer($payment)
                ->with('status', 'Your payment is already completed for this course.');
        }

        return view('cpd.payments.checkout', compact('payment'));
    }

    /**
     * Start payment with real gateway (or mock in dev).
     */
    public function start(Request $request, Payment $payment, CpdPaymentGateway $gateway)
    {
        $payment->load('enrolment.session.course', 'user');

        // If already paid, skip straight to player
        if ($payment->status === 'success') {
            return $this->redirectToPlayer($payment)
                ->with('status', 'Your payment is already completed for this course.');
        }

        $method = $request->input('method', 'card');

        // Store chosen method (card, orange_money, etc.)
        $payment->update([
            'method' => $method,
        ]);

        $session = $payment->enrolment->session;
        $course  = $session->course;

        // After paying on the gateway, user should land in the player
        $returnUrl   = route('cpd.learn.show', $course);
        $callbackUrl = route('cpd.payments.callback'); // for gateway server callback

        // Ask the gateway service where to send the user
        $redirectUrl = $gateway->createCheckoutRedirect($payment, $method, $returnUrl, $callbackUrl);

        return redirect()->away($redirectUrl);
    }

    /**
     * Mock "Pay Now" endpoint – used by gateway service in "mock" mode.
     */
    public function payMock(Request $request, Payment $payment, MoodleClient $moodle)
    {
        $payment->load('enrolment.session.course', 'user');

        // If already paid, just send to player
        if ($payment->status === 'success') {
            return $this->redirectToPlayer($payment)
                ->with('status', 'Your payment is already completed for this course.');
        }

        $method = $request->input('method', $payment->method ?? 'demo');

        $payment->update([
            'status' => 'success',
            'method' => $method,
        ]);

        $enrolment = $payment->enrolment;
        $enrolment->payment_status   = 'paid';
        $enrolment->enrolment_status = 'active';
        $enrolment->save();

        // 🔗 Enrol in Moodle (if moodle_course_id is set)
        try {
            $moodle->enrolFromEnrolment($enrolment);
        } catch (\Throwable $e) {
            \Log::error('Moodle enrolment failed', [
                'enrolment_id' => $enrolment->id,
                'error'        => $e->getMessage(),
            ]);
        }

        // ✅ After mock payment: go straight to the learning player
        return $this->redirectToPlayer($payment)
            ->with('status', 'Payment successful! You now have access to the online class.');
    }

    /**
     * Generic callback endpoint for real gateway (server-to-server).
     * You will adapt this to match the gateway’s parameters.
     */
    public function callback(Request $request, MoodleClient $moodle)
    {
        // Example pattern – adapt according to your provider:
        // e.g., ?reference=CPD-2025...&status=SUCCESS
        $reference = $request->input('reference');
        $status    = strtolower($request->input('status'));

        if (!$reference) {
            return response()->json(['message' => 'Missing reference'], 400);
        }

        $payment = Payment::where('local_reference', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Store raw callback for audit
        $payload = $payment->gateway_payload ?? [];
        $payload['callback'][] = $request->all();
        $payment->gateway_payload = $payload;

        if (in_array($status, ['success', 'paid', 'completed'])) {
            $payment->status = 'success';

            $enrolment = $payment->enrolment;
            $enrolment->payment_status   = 'paid';
            $enrolment->enrolment_status = 'active';
            $enrolment->save();

            // ✅ Enrol in Moodle after successful payment (real gateway)
            try {
                $moodle->enrolFromEnrolment($enrolment);
            } catch (\Throwable $e) {
                \Log::error('Moodle enrolment failed in callback', [
                    'enrolment_id' => $enrolment->id,
                    'error'        => $e->getMessage(),
                ]);
            }

        } elseif (in_array($status, ['failed', 'cancelled', 'error'])) {
            $payment->status = 'failed';
        }

        $payment->save();

        // Most gateways expect a 200 OK to stop retries
        return response()->json(['message' => 'OK']);
    }

    /**
     * Small helper: redirect this payment's user to the course player.
     */
    private function redirectToPlayer(Payment $payment)
    {
        $payment->loadMissing('enrolment.session.course');

        $session = $payment->enrolment->session;
        $course  = $session->course;

        // Route: /cpd/courses/{course}/learn/{lesson?}
        return redirect()->route('cpd.learn.show', $course);
    }
}
