<?php

namespace App\Http\Controllers;

use App\Models\Enrolment;
use Illuminate\Http\Request;

class CpdAdminEnrolmentController extends Controller
{
    /**
     * List CPD enrolments / registrations for admins.
     */
    public function index(Request $request)
    {
        // Base query with useful relationships preloaded
        $query = Enrolment::with([
            'user',
            'session.course.domain',
            'payments' => function ($q) {
                $q->latest();
            },
        ]);

        // ---- Filters ---------------------------------------------------
        if ($status = $request->input('status')) {
            $query->where('enrolment_status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('session.course', function ($q2) use ($search) {
                    $q2->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Paginated list
        $enrolments = $query
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        // Simple stats for the header
        $stats = [
            'total'   => Enrolment::count(),
            'active'  => Enrolment::where('enrolment_status', 'active')->count(),
            'pending' => Enrolment::where('enrolment_status', 'pending')->count(),
            'paid'    => Enrolment::where('payment_status', 'paid')->count(),
        ];

        return view('cpd.admin.enrolments.index', compact('enrolments', 'stats'));
    }
}
