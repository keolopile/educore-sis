<?php

namespace App\Http\Controllers;

use App\Models\CpdSession;
use App\Models\CpdRegistration;
use Illuminate\Http\Request;

class CpdSessionRegistrationController extends Controller
{
    public function create(CpdSession $session)
    {
        $course = $session->course;

        return view('cpd.sessions.register', [
            'session' => $session,
            'course'  => $course,
        ]);
    }

    public function store(Request $request, CpdSession $session)
    {
        $data = $request->validate([
            'full_name'            => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', 'max:255'],
            'organisation'         => ['nullable', 'string', 'max:255'],
            'role'                 => ['nullable', 'string', 'max:255'],
            'special_requirements' => ['nullable', 'string', 'max:1000'],
        ]);

        CpdRegistration::create([
            'cpd_session_id'       => $session->id,
            'user_id'              => auth()->id(),
            'full_name'            => $data['full_name'],
            'email'                => $data['email'],
            'organisation'         => $data['organisation'] ?? null,
            'role'                 => $data['role'] ?? null,
            'special_requirements' => $data['special_requirements'] ?? null,
            'status'               => 'pending_payment',
        ]);

        // ✅ redirect to the session detail page (handled by CpdSessionController@show)
        return redirect()
            ->route('cpd.sessions.show', $session)
            ->with('status', 'Registration details saved. Please proceed to payment.');
    }
}
