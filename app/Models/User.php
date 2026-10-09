<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'designation',
        'join_date',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'join_date' => 'date',
        ];
    }

    /**
     * Relationship to Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relationship to LeaveRequests.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    /**
     * Relationship to Attendances.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    /**
     * Calculate leave balances per leave type for a given year.
     */
    public function getLeaveBalances(?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $leaveTypes = LeaveType::all();

        $balances = [];
        foreach ($leaveTypes as $type) {
            $approvedUsed = (int) $this->leaveRequests()
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->whereYear('from_date', $year)
                ->sum('total_days');

            $pendingDays = (int) $this->leaveRequests()
                ->where('leave_type_id', $type->id)
                ->where('status', 'pending')
                ->whereYear('from_date', $year)
                ->sum('total_days');

            $maxDays = $type->max_days_per_year;
            $remaining = max(0, $maxDays - $approvedUsed);

            $balances[] = [
                'leave_type' => $type,
                'max_days' => $maxDays,
                'approved_used' => $approvedUsed,
                'pending_days' => $pendingDays,
                'remaining_balance' => $remaining,
            ];
        }

        return $balances;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Check if user has specific role or one of array of roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }
}
