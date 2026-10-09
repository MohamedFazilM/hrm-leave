<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Late Attendance Cutoff Time
    |--------------------------------------------------------------------------
    |
    | Any employee check-in after this time (24-hour format HH:MM) will be
    | marked with 'late' status.
    |
    */
    'late_cutoff' => env('HRM_LATE_CUTOFF', '09:30'),
];
