<?php

namespace App\Http\Controllers;

use App\Models\CpdSession;
use App\Models\Enrolment;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CpdSessionController extends Controller
{
    public function index()
    {
        $sessions = CpdSession::with('course.domain')
            ->where('status', 'open')
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->get();   // DataTables / frontend handles display

        return view('cpd.sessions.index', compact('sessions'));
    }

    public function show(CpdSession $session)
    {
        $session->load('course.domain');

        return view('cpd.sessions.show', [
            'session' => $session,
            'course'  => $session->course,
        ]);
    }

    public function registerForm(CpdSession $session)
    {
        if ($session->status !== 'open') {
            abort(404);
        }

        $course = $session->course;

        return view('cpd.sessions.register', [
            'session' => $session,
            'course'  => $course,
        ]);
    }

    public function registerStore(Request $request, CpdSession $session)
    {
        if ($session->status !== 'open') {
            abort(404);
        }

        $data = $request->validate([
            // Basic identity
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255',

            // Legacy / simple fields
            'organisation_name' => 'nullable|string|max:255',
            'position_title'    => 'nullable|string|max:255',

            // CCPD – individual info
            'id_number'         => 'nullable|string|max:100',
            'gender'            => 'nullable|in:male,female,other',
            'phone'             => 'nullable|string|max:50',
            'address'           => 'nullable|string|max:500',

            // CCPD – employment details
            'employer'          => 'nullable|string|max:255',
            'designation'       => 'nullable|string|max:255',
            'department'        => 'nullable|string|max:255',
            'work_phone'        => 'nullable|string|max:50',
            'work_email'        => 'nullable|email|max:255',

            // CCPD – sponsorship
            'sponsorship_type'  => 'nullable|in:self,employer',
        ]);

        // Find or create user by email
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'],
                // random password – they’ll normally login via SSO/email link later
                'password' => Hash::make(Str::password(16)),
            ]
        );

        // Keep name up to date
        if ($user->name !== $data['name']) {
            $user->name = $data['name'];
            $user->save();
        }

        // Already enrolled?
        $existing = Enrolment::where('user_id', $user->id)
            ->where('cpd_session_id', $session->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('cpd.sessions.show', $session)
                ->with('status', 'You are already registered for this session.');
        }

        // Capacity check
        if ($session->capacity && $session->seats_taken >= $session->capacity) {
            return back()
                ->withErrors(['session' => 'Sorry, this session is already full.'])
                ->withInput();
        }

        // Determine amount (session-specific price or course default)
        $amount   = $session->price ?: $session->course->default_price;
        $currency = $session->currency ?: $session->course->currency ?: 'BWP';

        // Create enrolment with extended CCPD fields
        $enrolment = Enrolment::create([
            'user_id'           => $user->id,
            'cpd_session_id'    => $session->id,
            'enrolment_status'  => 'pending',
            'payment_status'    => 'pending',

            // legacy / generic
            'organisation_name' => $data['organisation_name']
                ?? $data['employer']
                ?? null,
            'position_title'    => $data['position_title']
                ?? $data['designation']
                ?? null,

            // CCPD – personal
            'id_number'         => $data['id_number'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'phone'             => $data['phone'] ?? null,
            'address'           => $data['address'] ?? null,

            // CCPD – employment
            'employer'          => $data['employer'] ?? null,
            'designation'       => $data['designation'] ?? null,
            'department'        => $data['department'] ?? null,
            'work_phone'        => $data['work_phone'] ?? null,
            'work_email'        => $data['work_email'] ?? null,

            // CCPD – sponsorship
            'sponsorship_type'  => $data['sponsorship_type'] ?? null,
        ]);

        // Increment seat count (if capacity is tracked)
        if ($session->capacity) {
            $session->increment('seats_taken');
        }

        // Create initial payment record
        $payment = Payment::create([
            'user_id'           => $user->id,
            'enrolment_id'      => $enrolment->id,
            'amount'            => $amount,
            'currency'          => $currency,
            'method'            => null,
            'local_reference'   => 'CPD-' . now()->format('YmdHis') . '-' . $enrolment->id,
            'gateway_reference' => null,
            'status'            => 'pending',
            'gateway_payload'   => null,
        ]);

        return redirect()
            ->route('cpd.payments.checkout', $payment)
            ->with('status', 'Your registration has been captured. Please complete payment to confirm your place.');
    }

    public function thankyou(CpdSession $session)
    {
        $session->load('course.domain');

        return view('cpd.sessions.thankyou', [
            'session' => $session,
            'course'  => $session->course,
        ]);
    }
}

