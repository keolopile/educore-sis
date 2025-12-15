<?php

// app/Http/Controllers/CpdLessonProgressController.php
namespace App\Http\Controllers;

use App\Models\CpdLesson;
use App\Models\CpdLessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CpdLessonProgressController extends Controller
{
    public function store(Request $request, CpdLesson $lesson)
    {
        $user = Auth::user();

        $data = $request->validate([
            'seconds_watched'       => 'required|integer|min:0',
            'last_position_seconds' => 'required|integer|min:0',
            'completed'             => 'required|boolean',
        ]);

        $progress = CpdLessonProgress::firstOrNew([
            'user_id'       => $user->id,
            'cpd_lesson_id' => $lesson->id,
        ]);

        // Always keep the MAX watched time (don’t go backwards)
        $progress->seconds_watched = max(
            (int) $progress->seconds_watched,
            $data['seconds_watched']
        );

        $progress->last_position_seconds = $data['last_position_seconds'];

        if ($data['completed']) {
            $progress->completed = true;
        }

        $progress->save();

        return response()->json([
            'ok'        => true,
            'completed' => $progress->completed,
        ]);
    }
}
