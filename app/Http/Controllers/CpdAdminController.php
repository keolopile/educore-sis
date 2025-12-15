<?php

namespace App\Http\Controllers;

use App\Models\CpdCourse;
use App\Models\CpdSession;
use App\Models\Enrolment;
use App\Models\Payment;
use Illuminate\Http\Request;

class CpdAdminController extends Controller
{
    public function dashboard()
    {
        $coursesCount   = CpdCourse::count();
        $sessionsCount  = CpdSession::count();
        $upcomingCount  = CpdSession::whereDate('start_date', '>=', now()->toDateString())->count();
        $enrolments     = Enrolment::count();
        $paidEnrolments = Enrolment::where('payment_status', 'paid')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();

        return view('cpd.admin.dashboard', compact(
            'coursesCount',
            'sessionsCount',
            'upcomingCount',
            'enrolments',
            'paidEnrolments',
            'pendingPayments'
        ));
    }

    public function coursesIndex()
    {
        $courses = CpdCourse::with('domain', 'modules.lessons')
            ->orderBy('title')
            ->get();

        return view('cpd.admin.courses_index', compact('courses'));
    }
}
