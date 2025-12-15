<?php

namespace App\Http\Controllers;

use App\Models\CpdCourse;
use App\Models\CpdCourseModule;
use App\Models\CpdLesson;
use Illuminate\Http\Request;

class CpdCurriculumController extends Controller
{
    public function edit(CpdCourse $course)
    {
        $course->load(['modules.lessons' => function ($q) {
            $q->orderBy('position');
        }])->load(['modules' => function ($q) {
            $q->orderBy('position');
        }]);

        return view('cpd.admin.curriculum', [
            'course' => $course,
        ]);
    }

    public function storeModule(Request $request, CpdCourse $course)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! isset($data['position'])) {
            $data['position'] = $course->modules()->max('position') + 1;
        }

        $course->modules()->create($data);

        return back()->with('status', 'Module added.');
    }

    public function updateModule(Request $request, CpdCourseModule $module)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:1'],
        ]);

        $module->update($data);

        return back()->with('status', 'Module updated.');
    }

    public function destroyModule(CpdCourseModule $module)
    {
        $module->delete();

        return back()->with('status', 'Module deleted.');
    }

    public function storeLesson(Request $request, CpdCourseModule $module)
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'video_provider'  => ['required', 'in:file,vimeo,youtube,url'],
            'video_reference' => ['required', 'string', 'max:255'],
            'duration_seconds'=> ['nullable', 'integer', 'min:0'],
            'position'        => ['nullable', 'integer', 'min:1'],
            'is_preview'      => ['nullable', 'boolean'],
        ]);

        if (! isset($data['position'])) {
            $data['position'] = $module->lessons()->max('position') + 1;
        }

        $data['is_preview'] = (bool) ($data['is_preview'] ?? false);

        $module->lessons()->create($data);

        return back()->with('status', 'Lesson added.');
    }

    public function updateLesson(Request $request, CpdLesson $lesson)
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'video_provider'  => ['required', 'in:file,vimeo,youtube,url'],
            'video_reference' => ['required', 'string', 'max:255'],
            'duration_seconds'=> ['nullable', 'integer', 'min:0'],
            'position'        => ['nullable', 'integer', 'min:1'],
            'is_preview'      => ['nullable', 'boolean'],
        ]);

        $data['is_preview'] = (bool) ($data['is_preview'] ?? false);

        $lesson->update($data);

        return back()->with('status', 'Lesson updated.');
    }

    public function destroyLesson(CpdLesson $lesson)
    {
        $lesson->delete();

        return back()->with('status', 'Lesson deleted.');
    }
}
