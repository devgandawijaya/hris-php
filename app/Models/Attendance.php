<?php

namespace App\Models;

/**
 * Class Attendance
 * Alias of AttendanceRecord for standard HR naming compatibility.
 */
class Attendance extends AttendanceRecord
{
    protected $table = 'attendance_records';
}
