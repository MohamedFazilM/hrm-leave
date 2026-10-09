<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
        ];
    }

    /**
     * Relationship to Employee (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate formatted working hours & minutes.
     */
    public function getWorkDurationAttribute(): ?string
    {
        if (! $this->check_in || ! $this->check_out) {
            return null;
        }

        $mins = $this->check_in->diffInMinutes($this->check_out);
        $hours = floor($mins / 60);
        $remainingMins = $mins % 60;

        return "{$hours}h {$remainingMins}m";
    }

    /**
     * Check if attendance status is late.
     */
    public function isLate(): bool
    {
        return $this->status === 'late';
    }
}
