<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\Admission;
use App\Models\Registration;
use App\Models\Result;
use App\Models\User;

// Controllers
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\DtefImportController;
use App\Http\Controllers\CpdSessionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CpdCurriculumController;
use App\Http\Controllers\CpdLearningController;
use App\Http\Controllers\CpdLessonProgressController;
use App\Http\Controllers\CpdLessonController;
use App\Http\Controllers\CpdAdminController;
use App\Http\Controllers\CpdAdminEnrolmentController;

/*
|--------------------------------------------------------------------------
| Home / Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $stats = [
        'institutions'  => Institution::count(),
        'programmes'    => Programme::count(),
        'students'      => Student::count(),
        'admissions'    => Admission::count(),
        'registrations' => Registration::count(),
        'results'       => Result::count(),
    ];

    return view('dashboard', compact('stats'));
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| DTEF IMPORT (Register + Results)
|--------------------------------------------------------------------------
*/

// REGISTER import
Route::get('/dtef/import', [DtefImportController::class, 'showForm'])
    ->name('dtef.import.form');

Route::post('/dtef/import', [DtefImportController::class, 'handleUpload'])
    ->name('dtef.import.handle');

// RESULTS import
Route::get('/dtef/results-import', [DtefImportController::class, 'showResultForm'])
    ->name('dtef.results_import.form');

Route::post('/dtef/results-import', [DtefImportController::class, 'handleResultUpload'])
    ->name('dtef.results_import.handle');

/*
|--------------------------------------------------------------------------
| Core Data: Institutions, Programmes, Students
|--------------------------------------------------------------------------
*/

Route::get('/institutions', function () {
    $institutions = Institution::all();
    return view('institutions.index', compact('institutions'));
});

Route::get('/programmes', function () {
    $programmes = Programme::with('institution')->get();
    return view('programmes.index', compact('programmes'));
});

Route::get('/students', function () {
    $students = Student::with('institution')->get();
    return view('students.index', compact('students'));
});

/*
|--------------------------------------------------------------------------
| Admissions
|--------------------------------------------------------------------------
*/

Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');

Route::post('/admissions/{admission}/send-to-dtef', [AdmissionController::class, 'sendToDtef'])
    ->name('admissions.sendToDtef');

Route::post('/admissions/send-all-dtef', [AdmissionController::class, 'sendAllToDtef'])
    ->name('admissions.sendAllToDtef');

/*
|--------------------------------------------------------------------------
| Registrations
|--------------------------------------------------------------------------
*/

Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
Route::get('/registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');

Route::post('/registrations/{registration}/send-to-dtef', [RegistrationController::class, 'sendToDtef'])
    ->name('registrations.sendToDtef');

Route::post('/registrations/send-all-dtef', [RegistrationController::class, 'sendAllToDtef'])
    ->name('registrations.sendAllToDtef');

/*
|--------------------------------------------------------------------------
| Results
|--------------------------------------------------------------------------
*/

Route::get('/results', [ResultController::class, 'index'])->name('results.index');
Route::get('/results/create', [ResultController::class, 'create'])->name('results.create');
Route::post('/results', [ResultController::class, 'store'])->name('results.store');

Route::post('/results/{result}/send-to-dtef', [ResultController::class, 'sendToDtef'])
    ->name('results.sendToDtef');

Route::post('/results/send-all-to-dtef', [ResultController::class, 'sendAllToDtef'])
    ->name('results.sendAllToDtef');

// Batch send
Route::post('/results/send-pending-batch', [ResultController::class, 'sendPendingBatch'])
    ->name('results.sendPendingBatch');

/*
|--------------------------------------------------------------------------
| CPD Public Catalogue + Registration + Payments
|--------------------------------------------------------------------------
*/

Route::prefix('cpd')->name('cpd.')->group(function () {
    // Public list of sessions
    Route::get('/sessions', [CpdSessionController::class, 'index'])
        ->name('sessions.index');

    // Session detail page
    Route::get('/sessions/{session}', [CpdSessionController::class, 'show'])
        ->name('sessions.show');

    // Registration form
    Route::get('/sessions/{session}/register', [CpdSessionController::class, 'registerForm'])
        ->name('sessions.register');

    // Registration POST
    Route::post('/sessions/{session}/register', [CpdSessionController::class, 'registerStore'])
        ->name('sessions.register.store');

    // Thank you page (after payment)
    Route::get('/sessions/{session}/thank-you', [CpdSessionController::class, 'thankyou'])
        ->name('sessions.thankyou');

    // Payments
    Route::get('/payments/{payment}/checkout', [PaymentController::class, 'checkout'])
        ->name('payments.checkout');

    Route::post('/payments/{payment}/start', [PaymentController::class, 'start'])
        ->name('payments.start');

    // Mock payment endpoint (for testing)
    Route::match(['GET', 'POST'], '/payments/{payment}/pay-mock', [PaymentController::class, 'payMock'])
        ->name('payments.pay_mock');

    // Future real gateway callback
    Route::match(['GET', 'POST'], '/payments/callback', [PaymentController::class, 'callback'])
        ->name('payments.callback');
});

/*
|--------------------------------------------------------------------------
| TEMP: Simple Email-only Login
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (! $user) {
        return back()
            ->withErrors(['email' => 'We could not find a CPD account with that email.'])
            ->withInput();
    }

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->intended(route('cpd.my_courses'));

})->name('login.post');

/*
|--------------------------------------------------------------------------
| CPD Admin Area (dashboard + enrolments + curriculum)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'can:manage-cpd'])
    ->prefix('admin/cpd')
    ->name('admin.cpd.')
    ->group(function () {

        // Admin dashboard
        Route::get('/', [CpdAdminController::class, 'dashboard'])
            ->name('dashboard');

        // Courses list
        Route::get('/courses', [CpdAdminController::class, 'coursesIndex'])
            ->name('courses.index');

        // Enrolments dashboard
        Route::get('/enrolments', [CpdAdminEnrolmentController::class, 'index'])
            ->name('enrolments.index');

        Route::get('/enrolments/{enrolment}', [CpdAdminEnrolmentController::class, 'show'])
            ->name('enrolments.show');

        // Curriculum editor for a course
        Route::get('/courses/{course}/curriculum', [CpdCurriculumController::class, 'edit'])
            ->name('courses.curriculum.edit');

        // Modules
        Route::post('/courses/{course}/modules', [CpdCurriculumController::class, 'storeModule'])
            ->name('modules.store');
        Route::patch('/modules/{module}', [CpdCurriculumController::class, 'updateModule'])
            ->name('modules.update');
        Route::delete('/modules/{module}', [CpdCurriculumController::class, 'destroyModule'])
            ->name('modules.destroy');

        // Lessons
        Route::post('/modules/{module}/lessons', [CpdCurriculumController::class, 'storeLesson'])
            ->name('lessons.store');
        Route::patch('/lessons/{lesson}', [CpdCurriculumController::class, 'updateLesson'])
            ->name('lessons.update');
        Route::delete('/lessons/{lesson}', [CpdCurriculumController::class, 'destroyLesson'])
            ->name('lessons.destroy');
    });

/*
|--------------------------------------------------------------------------
| CPD Learning Player + Lesson Progress (learner side)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // "My CPD courses" dashboard
    Route::get('/cpd/my-courses', [CpdLearningController::class, 'myCourses'])
        ->name('cpd.my_courses');

    // Main course player (lesson optional -> fall back to first)
    Route::get('/cpd/courses/{course}/learn/{lesson?}', [CpdLearningController::class, 'show'])
        ->name('cpd.learn.show');

    // Optional direct lesson show route
    Route::get('/cpd/lessons/{lesson}', [CpdLessonController::class, 'show'])
        ->name('cpd.lessons.show');

    // Progress tracking
    Route::post('/cpd/lessons/{lesson}/progress', [CpdLessonProgressController::class, 'store'])
        ->name('cpd.lessons.progress');
});
