@extends('layouts.cpd_admin')

@section('title', 'Curriculum builder – ' . $course->title)

@section('content')
<style>
    .cpd-builder {
        max-width: 1320px;
        margin: 0 auto;
    }

    .cpd-builder-header {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
    }

    .cpd-breadcrumb {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    .cpd-breadcrumb a {
        color: #2563eb;
        text-decoration: none;
    }
    .cpd-breadcrumb a:hover {
        text-decoration: underline;
    }

    .cpd-builder-title {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .cpd-builder-sub {
        font-size: 13px;
        color: #4b5563;
        margin-top: 2px;
    }

    .cpd-builder-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        font-size: 12px;
        color: #4b5563;
    }

    .cpd-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        background: #ecfdf3;
        color: #166534;
        border: 1px solid #bbf7d0;
        white-space: nowrap;
    }

    .cpd-builder-actions .btn {
        font-size: 12px;
        padding: 5px 10px;
    }

    .cpd-builder-tag {
        font-size: 11px;
        color: #6b7280;
    }

    /* Layout */
    .cpd-builder-main {
        margin-top: 12px;
    }

    .cpd-sidebar-card {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
    }

    .cpd-sidebar-card .card-header {
        font-size: 13px;
        font-weight: 600;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .cpd-sidebar-card .card-body {
        font-size: 13px;
    }

    .cpd-module-mini {
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        padding: 8px 9px;
        margin-bottom: 8px;
        background: #ffffff;
    }
    .cpd-module-mini h6 {
        font-size: 13px;
        margin: 0 0 4px;
    }
    .cpd-module-mini small {
        font-size: 11px;
        color: #6b7280;
    }

    /* Module cards */
    .cpd-module-card {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
    }

    .cpd-module-card .card-header {
        font-size: 13px;
        font-weight: 600;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .cpd-module-meta {
        font-size: 11px;
        color: #6b7280;
    }

    /* Lessons table */
    .cpd-lessons-table {
        font-size: 12px;
        margin-bottom: 10px;
    }
    .cpd-lessons-table th,
    .cpd-lessons-table td {
        padding: 5px 6px;
        vertical-align: top;
    }
    .cpd-lessons-table thead th {
        background: #f3f4f6;
        font-weight: 600;
    }
    .cpd-lessons-table tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .cpd-form-label-sm {
        font-size: 11px;
        margin-bottom: 2px;
        color: #4b5563;
    }

    .cpd-input-sm,
    .cpd-select-sm,
    .cpd-textarea-sm {
        font-size: 12px;
        padding: 3px 6px;
    }

    .cpd-lesson-actions .btn {
        font-size: 11px;
        padding: 3px 7px;
    }

    .cpd-add-lesson {
        border-top: 1px dashed #e5e7eb;
        margin-top: 10px;
        padding-top: 10px;
    }

    .cpd-empty-state {
        font-size: 13px;
        color: #6b7280;
        padding: 10px;
        border-radius: 6px;
        background: #f9fafb;
        border: 1px dashed #e5e7eb;
    }

    @media (max-width: 768px) {
        .cpd-builder-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="cpd-builder py-3">

    {{-- HEADER --}}
    <div class="cpd-builder-header">
        <div>
            <div class="cpd-breadcrumb">
                <a href="{{ route('cpd.sessions.index') }}">CPD catalogue</a>
                <span>›</span>
                <span>Curriculum builder</span>
            </div>
            <h1 class="cpd-builder-title">
                Curriculum: {{ $course->title }}
            </h1>
            <p class="cpd-builder-sub">
                Define the learning path for this CPD course – create modules, attach video lessons,
                and control the sequence your participants follow.
            </p>
            <div class="cpd-builder-stats mt-1">
                @php
                    $moduleCount = $course->modules->count();
                    $lessonCount = $course->modules->flatMap->lessons->count();
                @endphp
                <span class="cpd-pill">
                    {{ $moduleCount }} module{{ $moduleCount === 1 ? '' : 's' }}
                </span>
                <span class="cpd-pill">
                    {{ $lessonCount }} lesson{{ $lessonCount === 1 ? '' : 's' }}
                </span>
                @if($course->updated_at)
                    <span class="cpd-builder-tag">
                        Last updated: {{ $course->updated_at->format('d M Y, H:i') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="cpd-builder-actions text-end">
            <div class="mb-1">
                <span class="badge bg-light text-muted border">
                    Curriculum builder
                </span>
            </div>
            <div>
                {{-- These can be wired later --}}
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" disabled>
                    Create modules
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                    Preview learner view
                </button>
            </div>
        </div>
    </div>

    {{-- FLASH --}}
    @if(session('status'))
        <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px;">
            {{ session('status') }}
        </div>
    @endif

    {{-- MAIN TWO-COLUMN LAYOUT --}}
    <div class="row cpd-builder-main">
        {{-- LEFT: MODULE PANEL --}}
        <div class="col-md-4 mb-3">
            <div class="card cpd-sidebar-card mb-3">
                <div class="card-header">
                    Add module
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.cpd.modules.store', $course) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="cpd-form-label-sm">Module title</label>
                            <input type="text" name="title"
                                   class="form-control form-control-sm cpd-input-sm"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label class="cpd-form-label-sm">Position (optional)</label>
                            <input type="number" name="position"
                                   class="form-control form-control-sm cpd-input-sm"
                                   min="1">
                            <small class="text-muted" style="font-size:11px;">
                                Lower numbers appear earlier in the learning path.
                            </small>
                        </div>
                        <button class="btn btn-primary w-100 btn-sm">
                            + Add module
                        </button>
                    </form>
                </div>
            </div>

            {{-- Quick module edit list --}}
            @foreach($course->modules as $module)
                <div class="cpd-module-mini">
                    <h6>{{ $module->position }}. {{ $module->title }}</h6>
                    <small>
                        {{ $module->lessons->count() }} lesson{{ $module->lessons->count() === 1 ? '' : 's' }}
                    </small>

                    <form method="post"
                          action="{{ route('admin.cpd.modules.update', $module) }}"
                          class="mt-2">
                        @csrf
                        @method('PATCH')
                        <div class="mb-1">
                            <label class="cpd-form-label-sm">Title</label>
                            <input type="text" name="title"
                                   class="form-control form-control-sm cpd-input-sm"
                                   value="{{ $module->title }}">
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div style="max-width:80px;">
                                <label class="cpd-form-label-sm">Position</label>
                                <input type="number" name="position"
                                       class="form-control form-control-sm cpd-input-sm"
                                       value="{{ $module->position }}">
                            </div>
                            <button class="btn btn-sm btn-outline-primary">
                                Save
                            </button>
                            <form method="post"
                                  action="{{ route('admin.cpd.modules.destroy', $module) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete module and all its lessons?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- RIGHT: MODULES + LESSONS --}}
        <div class="col-md-8">
            @forelse($course->modules as $module)
                <div class="card cpd-module-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span>{{ $module->position }}. {{ $module->title }}</span>
                        </div>
                        <div class="cpd-module-meta">
                            {{ $module->lessons->count() }} lesson{{ $module->lessons->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Existing lessons --}}
                        @if($module->lessons->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm cpd-lessons-table align-middle">
                                    <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Lesson</th>
                                        <th style="width:220px;">Video</th>
                                        <th style="width:80px;">Duration (s)</th>
                                        <th style="width:70px;">Preview</th>
                                        <th style="width:130px;">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($module->lessons as $lesson)
                                        <tr>
                                            <td>{{ $lesson->position }}</td>
                                            <td style="min-width:200px;">
                                                <form method="post"
                                                      action="{{ route('admin.cpd.lessons.update', $lesson) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="cpd-form-label-sm">Title</label>
                                                    <input type="text" name="title"
                                                           value="{{ $lesson->title }}"
                                                           class="form-control form-control-sm cpd-input-sm mb-1">
                                                    <label class="cpd-form-label-sm">Short description</label>
                                                    <textarea name="description"
                                                              class="form-control form-control-sm cpd-textarea-sm"
                                                              rows="2"
                                                              placeholder="Short description">{{ $lesson->description }}</textarea>
                                            </td>
                                            <td>
                                                    <div class="mb-1">
                                                        <label class="cpd-form-label-sm">Provider</label>
                                                        <select name="video_provider"
                                                                class="form-select form-select-sm cpd-select-sm">
                                                            @foreach(['file','vimeo','youtube','url'] as $provider)
                                                                <option value="{{ $provider }}"
                                                                    @selected($lesson->video_provider === $provider)>
                                                                    {{ ucfirst($provider) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="cpd-form-label-sm">Video ref / URL</label>
                                                        <input type="text" name="video_reference"
                                                               value="{{ $lesson->video_reference }}"
                                                               class="form-control form-control-sm cpd-input-sm"
                                                               placeholder="File path / ID / URL">
                                                    </div>
                                            </td>
                                            <td>
                                                    <label class="cpd-form-label-sm">Duration</label>
                                                    <input type="number" name="duration_seconds"
                                                           value="{{ $lesson->duration_seconds }}"
                                                           class="form-control form-control-sm cpd-input-sm"
                                                           min="0">
                                            </td>
                                            <td class="text-center">
                                                    <label class="cpd-form-label-sm d-block">Preview</label>
                                                    <input type="checkbox" name="is_preview" value="1"
                                                           {{ $lesson->is_preview ? 'checked' : '' }}>
                                            </td>
                                            <td class="cpd-lesson-actions text-end">
                                                    <div class="mb-1">
                                                        <label class="cpd-form-label-sm d-block">Position</label>
                                                        <input type="number" name="position"
                                                               value="{{ $lesson->position }}"
                                                               class="form-control form-control-sm cpd-input-sm"
                                                               style="max-width:80px; margin-left:auto;">
                                                    </div>
                                                    <div class="mt-1">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            Save
                                                        </button>
                                                    </form>
                                                        <form method="post"
                                                              action="{{ route('admin.cpd.lessons.destroy', $lesson) }}"
                                                              style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Delete this lesson?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="cpd-empty-state mb-2">
                                No lessons yet for this module. Use the form below to add your first video lesson.
                            </div>
                        @endif

                        {{-- Add lesson --}}
                        <div class="cpd-add-lesson">
                            <form method="post" action="{{ route('admin.cpd.lessons.store', $module) }}">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="cpd-form-label-sm">Lesson title</label>
                                        <input type="text" name="title"
                                               class="form-control form-control-sm cpd-input-sm"
                                               required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="cpd-form-label-sm">Provider</label>
                                        <select name="video_provider"
                                                class="form-select form-select-sm cpd-select-sm">
                                            <option value="url">URL</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="vimeo">Vimeo</option>
                                            <option value="file">File</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="cpd-form-label-sm">Video ref / URL</label>
                                        <input type="text" name="video_reference"
                                               class="form-control form-control-sm cpd-input-sm"
                                               placeholder="Paste video URL or ID"
                                               required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="cpd-form-label-sm">Dur (s)</label>
                                        <input type="number" name="duration_seconds"
                                               class="form-control form-control-sm cpd-input-sm"
                                               min="0">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="cpd-form-label-sm">Preview</label><br>
                                        <input type="checkbox" name="is_preview" value="1">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="cpd-form-label-sm">Short description (optional)</label>
                                    <textarea name="description" rows="2"
                                              class="form-control form-control-sm cpd-textarea-sm"
                                              placeholder="Describe what participants will learn."></textarea>
                                </div>
                                <div class="mt-2 text-end">
                                    <button class="btn btn-sm btn-success">
                                        + Add lesson
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            @empty
                <div class="cpd-empty-state">
                    No modules yet. Use the panel on the left to create your first module
                    (for example: <em>Introduction</em>, <em>Core skills</em>, etc.).
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
