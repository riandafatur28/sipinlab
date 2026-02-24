<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_name',
        'course_name',
        'course_code',
        'class_name',
        'golongan',
        'lecturer_id',
        'semester',
        'day',
        'start_time',
        'end_time',
        'session',
        'students_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'semester' => 'integer',
        'students_count' => 'integer',
    ];

    // Relationship to lecturer
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    // Scope for active schedules
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for specific day
    public function scopeForDay($query, $day)
    {
        return $query->where('day', $day);
    }

    // Scope for specific lab
    public function scopeForLab($query, $labName)
    {
        return $query->where('lab_name', $labName);
    }
}