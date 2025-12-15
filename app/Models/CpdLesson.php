<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpdLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpd_course_module_id',
        'title',
        'description',
        'video_provider',
        'video_reference',
        'duration_seconds',
        'position',
        'is_preview',
    ];

    public function module()
    {
        return $this->belongsTo(CpdCourseModule::class, 'cpd_course_module_id');
    }

    public function course()
    {
        return $this->module?->course();
    }

    public function progresses()
    {
        return $this->hasMany(CpdLessonProgress::class);
    }

    /**
     * Helper: return an embed URL or file URL depending on provider.
     */
    public function videoUrl(): ?string
    {
        if (! $this->video_reference) {
            return null;
        }

        switch ($this->video_provider) {
            case 'youtube':
                // store just the ID, e.g. dQw4w9WgXcQ
                return 'https://www.youtube.com/embed/' . $this->video_reference;

            case 'vimeo':
                return 'https://player.vimeo.com/video/' . $this->video_reference;

            case 'file':
                // stored via Storage (public disk etc.)
                return \Storage::url($this->video_reference);

            case 'url':
            default:
                // already a full URL
                return $this->video_reference;
        }
    }
}
