<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class CpdPaymentGateway
{
    /**
     * Return the URL the user should be redirected to in order to pay.
     * In "mock" mode we just point to the internal pay_mock route.
     * In "http" mode we call a real gateway API and use its redirect URL.
     */
    public function createCheckoutRedirect(Payment $payment, string $method, string $returnUrl, string $callbackUrl): string
    {
        $mode = config('payments.gateway', 'mock');

        if ($mode === 'mock') {
            // Keep your current behaviour: internal fake payment
            return route('cpd.payments.pay_mock', $payment);
        }

        // ───── Real HTTP gateway mode (you plug in your provider here) ─────
        $initUrl = config('payments.init_url');
        $secret  = config('payments.secret');

        // Build payload according to your gateway’s docs
        $payload = [
            'amount'          => $payment->amount,
            'currency'        => $payment->currency,
            'reference'       => $payment->local_reference,
            'customer_email'  => $payment->user->email,
            'customer_name'   => $payment->user->name,
            'return_url'      => $returnUrl,   // where the user is sent after payment
            'callback_url'    => $callbackUrl, // server-to-server notification
            'payment_method'  => $method,
        ];

        // Example POST – adjust headers / structure to match your gateway
        $response = Http::withToken($secret)
            ->post($initUrl, $payload)
            ->throw()
            ->json();

        // Expect the gateway to return something like:
        // { "checkout_url": "https://gateway.example.com/pay/XYZ", "reference": "XYZ" }

        $checkoutUrl = $response['checkout_url'] ?? null;
        $gatewayRef  = $response['reference']    ?? null;

        if ($gatewayRef) {
            $payment->update([
                'gateway_reference' => $gatewayRef,
                'gateway_payload'   => $response,
            ]);
        }

        if (!$checkoutUrl) {
            // Fall back to mock if gateway fails
            return route('cpd.payments.pay_mock', $payment);
        }

        return $checkoutUrl;
    }
}
