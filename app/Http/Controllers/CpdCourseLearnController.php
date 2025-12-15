<?php

namespace App\Http\Controllers;

use App\Models\CpdCourse;
use App\Models\CpdLesson;
use App\Models\CpdLessonProgress;
use Illuminate\Http\Request;

class CpdCourseLearnController extends Controller
{
    public function show(CpdCourse $course, CpdLesson $lesson)
    {
        // 1) Make sure user is logged in
        $user = auth()->user();
        if (! $user) {
            abort(403, 'You must be logged in to view this lesson.');
        }

        // 2) Make sure module + lessons are loaded for navigation
        //    (also load quizzes + options for the pop-ups)
        $lesson->loadMissing('module.lessons', 'quizzes.options');

        $module = $lesson->module;

        if (! $module) {
            // This lesson is not attached to a module – better to fail nicely
            abort(404, 'Lesson module not found.');
        }

        // All lessons in this module, ordered
        $moduleLessons = $module->lessons
            ->sortBy('position')
            ->values();

        // 3) Verify this lesson is actually part of the module list
        $index = $moduleLessons->search(function ($l) use ($lesson) {
            return $l->id === $lesson->id;
        });

        if ($index === false) {
            abort(404, 'Lesson not part of this module.');
        }

        // 4) Check if any previous lesson is NOT completed
        $locked = false;
        $firstIncompleteLesson = null;

        if ($index > 0) {
            $previousIds = $moduleLessons->slice(0, $index)->pluck('id');

            // Grab all progress rows for previous lessons
            $progressRows = CpdLessonProgress::where('user_id', $user->id)
                ->whereIn('cpd_lesson_id', $previousIds)
                ->get()
                ->keyBy('cpd_lesson_id');

            // A lesson is considered incomplete if:
            //  - no progress row at all, OR
            //  - progress row exists but completed == false
            $incompleteIds = $previousIds->filter(function ($lessonId) use ($progressRows) {
                $row = $progressRows->get($lessonId);

                return ! $row || ! $row->completed;
            });

            if ($incompleteIds->isNotEmpty()) {
                $locked = true;
                $firstIncompleteId = $incompleteIds->first();
                $firstIncompleteLesson = $moduleLessons->firstWhere('id', $firstIncompleteId);
            }
        }

        if ($locked) {
            // Option A: just bounce back
            // return redirect()
            //     ->back()
            //     ->with('error', 'Please complete the previous lesson(s) first.');

            // Option B (nicer UX): send them to the first incomplete lesson
            if ($firstIncompleteLesson) {
                return redirect()
                    ->route('cpd.courses.learn', [$course, $firstIncompleteLesson])
                    ->with('error', 'Please complete the previous lesson(s) first.');
            }

            return redirect()
                ->back()
                ->with('error', 'Please complete the previous lesson(s) first.');
        }

        // 5) Progress row for THIS lesson (for resume / seconds watched)
        $progress = CpdLessonProgress::firstOrCreate(
            [
                'user_id'       => $user->id,
                'cpd_lesson_id' => $lesson->id,
            ],
            [
                'seconds_watched'       => 0,
                'last_position_seconds' => 0,
                'completed'             => false,
            ]
        );

        // 6) All modules (with lessons) for the left sidebar (optional but nice)
        $modules = $course->modules()
            ->with('lessons')
            ->orderBy('position')
            ->get();

        return view('cpd.courses.learn', [
            'course'   => $course,
            'lesson'   => $lesson,
            'modules'  => $modules,     // used by the view for the left nav
            'quizzes'  => $lesson->quizzes,  // quizzes already eager loaded
            'progress' => $progress,
        ]);
    }
}
