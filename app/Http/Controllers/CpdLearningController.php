<?php

namespace App\Http\Controllers;

use App\Models\CpdCourse;
use App\Models\CpdLesson;
use App\Models\CpdLessonProgress;
use Illuminate\Http\Request;
use App\Models\Enrolment;

class CpdLearningController extends Controller
{
    public function show(Request $request, CpdCourse $course, ?int $lesson = null)
    {
        // Load modules + lessons for the sidebar
        $modules = $course->modules()->with('lessons')->get();

        // Pick current lesson:
        if ($lesson) {
            $currentLesson = CpdLesson::whereIn(
                'cpd_course_module_id',
                $modules->pluck('id')
            )->where('id', $lesson)->first();
        } else {
            // Default = first lesson of first module
            $currentLesson = $modules
                ->flatMap->lessons
                ->sortBy('position')
                ->first();
        }

        if (! $currentLesson) {
            // No lessons configured yet
            return view('cpd.learning.empty', compact('course'));
        }

        // Fetch user progress (if any)
        $progress = CpdLessonProgress::where('user_id', $request->user()->id)
            ->where('cpd_lesson_id', $currentLesson->id)
            ->first();

        return view('cpd.learning.show', [
            'course'        => $course,
            'modules'       => $modules,
            'currentLesson' => $currentLesson,
            'progress'      => $progress,
        ]);
    }

    public function storeProgress(Request $request, CpdLesson $lesson)
    {
        $userId = $request->user()->id;

        CpdLessonProgress::updateOrCreate(
            ['user_id' => $userId, 'cpd_lesson_id' => $lesson->id],
            [
                'completed'        => $request->boolean('completed', true),
                'seconds_watched'  => $request->input('seconds_watched', 0),
            ]
        );

        return back()->with('status', 'Lesson marked as completed.');
    }




public function myCourses(Request $request)
{
    $user = $request->user();

    $enrolments = Enrolment::with('session.course.domain')
        ->where('user_id', $user->id)
        ->whereIn('enrolment_status', ['active', 'completed'])
        ->orderByDesc('created_at')
        ->get();

    return view('cpd.my_courses', compact('enrolments'));
}










}
