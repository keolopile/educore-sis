<?php
// database/seeders/CpdSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CpdDomain;
use App\Models\CpdCourse;
use App\Models\CpdSession;
use Carbon\Carbon;

class CpdSeeder extends Seeder
{
    public function run(): void
    {
        $domain = CpdDomain::create([
            'name'        => 'Leadership & Management',
            'slug'        => 'leadership-management',
            'description' => 'Short courses focused on leadership, governance, and management.',
        ]);

        $course = CpdCourse::create([
            'cpd_domain_id'     => $domain->id,
            'code'              => 'CPD-LEAD-001',
            'title'             => 'Leadership in Education & Training',
            'short_description' => 'A practical programme for middle to senior managers.',
            'full_description'  => 'You can paste your full CCPD description here...',
            'duration_days'     => 3,
            'cpd_points'        => 15,
            'default_price'     => 4500,
            'currency'          => 'BWP',
        ]);

        CpdSession::create([
            'cpd_course_id'  => $course->id,
            'start_date'     => Carbon::parse('2025-04-10'),
            'end_date'       => Carbon::parse('2025-04-12'),
            'delivery_mode'  => 'online',
            'location'       => 'Online (Moodle + Zoom)',
            'price'          => 4500,
            'currency'       => 'BWP',
            'capacity'       => 30,
            'seats_taken'    => 0,
            'moodle_course_id' => 123, // replace with real Moodle course ID
            'status'         => 'open',
        ]);
    }
}
