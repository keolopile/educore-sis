<?php

namespace App\Services;

use App\Models\Enrolment;
use App\Models\CpdCourse;

use Illuminate\Support\Facades\Http;

class MoodleClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        // Use defaults to avoid nulls
        $this->baseUrl = rtrim((string) config('moodle.base_url', ''), '/');
        $this->token   = (string) config('moodle.token', '');

        if ($this->token === '') {
            \Log::warning('MoodleClient initialised without MOODLE_TOKEN configured.');
        }
    }

    /**
     * Low-level call helper for Moodle REST web services.
     */
    protected function call(string $function, array $params = []): mixed
    {
        if ($this->token === '') {
            throw new \RuntimeException('Moodle token not configured. Set MOODLE_TOKEN in .env.');
        }

        $response = Http::asForm()->post(
            $this->baseUrl . '/webservice/rest/server.php',
            array_merge([
                'wstoken'            => $this->token,
                'moodlewsrestformat' => 'json',
                'wsfunction'         => $function,
            ], $params)
        );

        $json = $response->json();

        // Moodle errors come as { exception: "...", message: "..." }
        if (is_array($json) && isset($json['exception'])) {
            throw new \RuntimeException("Moodle error ({$json['exception']}): {$json['message']}");
        }

        return $json;
    }

    /**
     * Simple debug helper – returns site info so we can test connectivity.
     */
    public function getSiteInfo(): array
    {
        return (array) $this->call('core_webservice_get_site_info');
    }

    /**
     * Ensure a Moodle course exists for the given CPD course.
     * If it doesn't, create it and store moodle_course_id on the CPD course.
     */
    public function ensureMoodleCourseForCpdCourse(CpdCourse $course): ?int
    {
        // If already linked, just return
        if ($course->moodle_course_id) {
            return $course->moodle_course_id;
        }

        // Decide Moodle category ID for CPD – configure in config/moodle.php + .env
        $categoryId = (int) config('moodle.cpd_category_id', 0);
        if ($categoryId <= 0) {
            throw new \RuntimeException('CPD Moodle category not configured (moodle.cpd_category_id).');
        }

        $fullname  = $course->title;
        $shortname = $course->code; // e.g. CPD-LEAD-001
        $idnumber  = $course->code; // external code

        $result = $this->call('core_course_create_courses', [
            'courses' => [[
                'fullname'   => $fullname,
                'shortname'  => $shortname,
                'categoryid' => $categoryId,
                'idnumber'   => $idnumber,
                'summary'    => $course->short_description ?? '',
                'format'     => 'topics',
                'visible'    => 1,
            ]],
        ]);

        if (!is_array($result) || empty($result[0]['id'])) {
            throw new \RuntimeException('Failed to create Moodle course for CPD course '.$course->id);
        }

        $moodleId = (int) $result[0]['id'];

        $course->moodle_course_id = $moodleId;
        $course->save();

        return $moodleId;
    }

    /**
     * Ensure a Moodle user exists for this enrolment’s user (by email).
     * Returns Moodle user id.
     */
    public function ensureMoodleUser(Enrolment $enrolment): int
    {
        $user = $enrolment->user;

        // 1) Look up by email
        $existing = $this->call('core_user_get_users_by_field', [
            'field'  => 'email',
            'values' => [$user->email],
        ]);

        if (!empty($existing) && isset($existing[0]['id'])) {
            return (int) $existing[0]['id'];
        }

        // 2) Create if not found
        $username = $this->makeUsernameFromEmail($user->email);

        $created = $this->call('core_user_create_users', [
            'users' => [[
                'username'   => $username,
                'password'   => 'IdmTemp!' . rand(1000, 9999), // can be forced reset later
                'firstname'  => $user->name,
                'lastname'   => 'CPD',
                'email'      => $user->email,
                'auth'       => 'manual',
                'lang'       => 'en',
            ]],
        ]);

        return (int) $created[0]['id'];
    }

    /**
     * Enrol the Moodle user into the given course id (manual enrol).
     */
    public function enrolUserToCourse(int $moodleUserId, int $moodleCourseId, string $role = 'student'): void
    {
        // Moodle default student roleid is usually 5 – adjust if different
        $roleId = $role === 'student' ? 5 : 5;

        $this->call('enrol_manual_enrol_users', [
            'enrolments' => [[
                'roleid'   => $roleId,
                'userid'   => $moodleUserId,
                'courseid' => $moodleCourseId,
            ]],
        ]);
    }

    protected function makeUsernameFromEmail(string $email): string
    {
        $base = strstr($email, '@', true);
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $base);
    }

    /**
     * Full flow: ensure user, enrol in course, update enrolment.moodle_user_id
     */
    public function enrolFromEnrolment(Enrolment $enrolment): void
    {
        $session = $enrolment->session;
        $course  = $session->course ?? null;

        if (!$course) {
            // CPD course not loaded / missing relation
            return;
        }

        // Prefer course-level mapping, fall back to session->moodle_course_id for backward compatibility
        $moodleCourseId = $course->moodle_course_id ?? $session->moodle_course_id ?? null;

        // If still no Moodle course, optionally auto-create it for online/hybrid runs
        if (!$moodleCourseId && in_array($session->delivery_mode, ['online', 'hybrid'])) {
            $moodleCourseId = $this->ensureMoodleCourseForCpdCourse($course);
        }

        if (!$moodleCourseId) {
            // nothing to do – no Moodle course mapping
            return;
        }

        $moodleUserId = $this->ensureMoodleUser($enrolment);

        $this->enrolUserToCourse($moodleUserId, (int) $moodleCourseId);

        $enrolment->moodle_user_id = $moodleUserId;
        $enrolment->save();
    }
}
