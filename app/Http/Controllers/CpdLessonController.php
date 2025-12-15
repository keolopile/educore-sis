<?php

namespace App\Http\Controllers;

use App\Models\CpdLesson;
use Illuminate\Http\Request;

class CpdLessonController extends Controller
{
    public function show(Request $request, CpdLesson $lesson)
    {
        $lesson->load('module.course');

        $progress = null;
        if ($request->user()) {
            $progress = $request->user()->progressForLesson($lesson);
        }

        return view('cpd.lessons.show', [
            'lesson'   => $lesson,
            'course'   => $lesson->module->course,
            'module'   => $lesson->module,
            'progress' => $progress,
            'videoUrl' => $lesson->videoUrl(),
        ]);
    }
}
