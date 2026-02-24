<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Booking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookings';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // User & Lab Info
        'user_id',
        'lab_name',
        'session',

        // Time & Date
        'start_time',
        'end_time',
        'booking_date',
        'duration_days',
        'start_date',
        'end_date',

        // Activity & Purpose
        'activity',
        'purpose',
        'notes',

        // ✅ Contact Info
        'phone',
        'prodi',
        'golongan',

        // ✅ Group Booking
        'is_group',
        'members',           // JSON array of member IDs
        'supervisor_id',     // For student bookings

        // Approval Workflow
        'status',            // pending, approved_dosen, approved_teknisi, confirmed, rejected, cancelled

        // Approval by Dosen
        'approved_by_dosen',
        'approved_at_dosen',

        // Approval by Teknisi
        'approved_by_teknisi',
        'approved_at_teknisi',

        // Approval by Ka Lab
        'approved_by_kalab',
        'approved_at_kalab',

        // Rejection
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'duration_days' => 'integer',
        'is_group' => 'boolean',
        'members' => 'array',
        'booking_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'approved_at_dosen' => 'datetime',
        'approved_at_teknisi' => 'datetime',
        'approved_at_kalab' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedByDosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_dosen');
    }

    public function approvedByTeknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_teknisi');
    }

    public function approvedByKalab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_kalab');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function getMembersCollectionAttribute()
    {
        if (empty($this->members) || !is_array($this->members)) {
            return collect();
        }
        return User::whereIn('id', $this->members)->get();
    }

    public function membersList(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'booking_members',
            'booking_id',
            'user_id'
        )->withTimestamps();
    }

    // ========================================================================
    // STATUS HELPERS
    // ========================================================================

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApprovedByDosen(): bool { return $this->status === 'approved_dosen'; }
    public function isApprovedByTeknisi(): bool { return $this->status === 'approved_teknisi'; }
    public function isApprovedByKalab(): bool { return $this->status === 'approved_kalab'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    
    public function isActive(): bool
    {
        return !in_array($this->status, ['rejected', 'cancelled']);
    }

    // ========================================================================
    // APPROVAL FLOW - Permission Checks
    // ========================================================================

    public function canApproveByDosen(): bool
    {
        return $this->isPending() && $this->user?->role === 'mahasiswa';
    }

    public function canApproveByTeknisi(): bool
    {
        if ($this->user?->role === 'mahasiswa') {
            return $this->isApprovedByDosen();
        }
        // ✅ Dosen bookings can be approved by teknisi from 'pending' status
        return in_array($this->status, ['pending', 'approved_dosen']);
    }

        /**
     * ✅ Check if booking can be approved by current authenticated technician
     * Includes lab assignment validation
     */
    public function canApproveByCurrentTeknisi(): bool
    {
        $user = auth()->user();
        
        // Hanya teknisi yang bisa approve
        if ($user->role !== 'teknisi') {
            return false;
        }
        
        // ✅ Teknisi hanya bisa approve booking untuk lab-nya sendiri
        if ($user->lab_name !== $this->lab_name) {
            return false;
        }
        
        // Status harus pending atau approved_dosen
        return in_array($this->status, ['pending', 'approved_dosen']);
    }

    public function canApproveByKalab(): bool
    {
        return $this->isApprovedByTeknisi();
    }

    public function canBeRejected(): bool
    {
        return !in_array($this->status, ['confirmed', 'rejected', 'cancelled']);
    }

    public function canBeCancelledByOwner(int $userId): bool
    {
        return $this->user_id === $userId && !$this->isConfirmed();
    }

    // ========================================================================
    // BOOKING TYPE HELPERS
    // ========================================================================

    public function isStudentBooking(): bool
    {
        return $this->user?->role === 'mahasiswa';
    }

    public function isLecturerBooking(): bool
    {
        return $this->user?->role === 'dosen';
    }

    public function isGroupBooking(): bool
    {
        return $this->is_group === true && !empty($this->members);
    }

    public function isIndividualBooking(): bool
    {
        return !$this->is_group || empty($this->members);
    }

    // ========================================================================
    // APPROVAL FLOW - Progress & Status
    // ========================================================================

    public function getCurrentApprovalStep(): string
    {
        // ✅ Custom step for dosen bookings (skip dosen approval)
        if ($this->isLecturerBooking()) {
            return match($this->status) {
                'pending' => 'Menunggu Persetujuan Teknisi',
                'approved_teknisi' => 'Menunggu Persetujuan Ka Lab',
                'confirmed' => 'Booking Dikonfirmasi',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
                default => 'Status Tidak Dikenal',
            };
        }
        
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan Dosen',
            'approved_dosen' => 'Menunggu Persetujuan Teknisi',
            'approved_teknisi' => 'Menunggu Persetujuan Ka Lab',
            'confirmed' => 'Booking Dikonfirmasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => 'Status Tidak Dikenal',
        };
    }

    public function getApprovalProgress(): int
    {
        // ✅ Custom progress for dosen bookings (2 steps instead of 3)
        if ($this->isLecturerBooking()) {
            return match($this->status) {
                'pending' => 0,
                'approved_teknisi' => 50,
                'confirmed' => 100,
                'rejected', 'cancelled' => 0,
                default => 0,
            };
        }
        
        return match($this->status) {
            'pending' => 0,
            'approved_dosen' => 33,
            'approved_teknisi' => 66,
            'confirmed' => 100,
            'rejected', 'cancelled' => 0,
            default => 0,
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'approved_dosen' => 'bg-blue-100 text-blue-800 border-blue-300',
            'approved_teknisi' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'confirmed' => 'bg-green-100 text-green-800 border-green-300',
            'rejected' => 'bg-red-100 text-red-800 border-red-300',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getStatusLabel(): string
    {
        // ✅ Custom label for dosen bookings
        if ($this->isLecturerBooking()) {
            return match($this->status) {
                'pending' => 'Menunggu Teknisi',
                'approved_teknisi' => 'Disetujui Teknisi',
                'confirmed' => 'Dikonfirmasi ✅',
                'rejected' => 'Ditolak ❌',
                'cancelled' => 'Dibatalkan',
                default => ucfirst($this->status),
            };
        }
        
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved_dosen' => 'Disetujui Dosen',
            'approved_teknisi' => 'Disetujui Teknisi',
            'confirmed' => 'Dikonfirmasi ✅',
            'rejected' => 'Ditolak ❌',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    // ========================================================================
    // DATE & TIME ACCESSORS
    // ========================================================================

    public function getFormattedBookingDateAttribute(): string
    {
        if (!$this->booking_date) return '-';
        return Carbon::parse($this->booking_date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getFormattedStartDateAttribute(): string
    {
        if (!$this->start_date) return '-';
        return Carbon::parse($this->start_date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getFormattedEndDateAttribute(): string
    {
        if (!$this->end_date) return '-';
        return Carbon::parse($this->end_date)->locale('id')->isoFormat('D MMMM Y');
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time . ' - ' . $this->end_time;
        }
        return '-';
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) return '-';
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        if ($start->eq($end)) {
            return $start->locale('id')->isoFormat('D MMMM Y');
        }
        return $start->locale('id')->isoFormat('D MMM') . ' - ' . $end->locale('id')->isoFormat('D MMMM Y');
    }

    public function getFormattedStartDateTimeAttribute(): string
    {
        if (!$this->start_date || !$this->start_time) return '-';
        return Carbon::parse($this->start_date . ' ' . $this->start_time)
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y [pukul] HH:mm');
    }

    public function getFormattedEndDateTimeAttribute(): string
    {
        if (!$this->end_date || !$this->end_time) return '-';
        return Carbon::parse($this->end_date . ' ' . $this->end_time)
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y [pukul] HH:mm');
    }

    // ========================================================================
    // DURATION & VALIDATION HELPERS
    // ========================================================================

    public function getDurationInDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return $this->duration_days ?? 1;
        }
        return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
    }

    public function isPastBooking(): bool
    {
        if (!$this->end_date) return false;
        return Carbon::parse($this->end_date)->isPast();
    }

    public function isTodayBooking(): bool
    {
        if (!$this->booking_date) return false;
        return Carbon::parse($this->booking_date)->isToday();
    }

    public function isFutureBooking(): bool
    {
        if (!$this->booking_date) return false;
        return Carbon::parse($this->booking_date)->isFuture();
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->isTodayBooking() || !$this->start_time || !$this->end_time) {
            return false;
        }
        $now = Carbon::now('Asia/Jakarta')->format('H:i');
        return $now >= $this->start_time && $now < $this->end_time;
    }

    // ========================================================================
    // CONTACT & USER INFO ACCESSORS
    // ========================================================================

    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) return '-';
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($phone) >= 11 && substr($phone, 0, 2) === '08') {
            return '+62' . substr($phone, 1);
        }
        return $this->phone;
    }

    public function getUserWithRoleAttribute(): string
    {
        if (!$this->user) return 'Unknown';
        $roleBadge = match($this->user->role) {
            'mahasiswa' => '🎓',
            'dosen' => '👨‍🏫',
            'teknisi' => '🔧',
            'ketua_lab' => '👔',
            'admin' => '⚙️',
            default => '',
        };
        return "{$roleBadge} {$this->user->name}";
    }

    // ========================================================================
    // ACTIVITY & PURPOSE HELPERS
    // ========================================================================

    public function getDisplayActivityAttribute(): string
    {
        if ($this->activity === 'Lainnya' && $this->notes) {
            return $this->notes;
        }
        return $this->activity ?? '-';
    }

    public function getTruncatedPurposeAttribute(int $length = 100): string
    {
        if (!$this->purpose) return '-';
        if (strlen($this->purpose) <= $length) {
            return $this->purpose;
        }
        return substr($this->purpose, 0, $length) . '...';
    }

    // ========================================================================
    // SCOPES - Query Builders
    // ========================================================================

    public function scopePending(Builder $query): Builder { return $query->where('status', 'pending'); }
    public function scopeConfirmed(Builder $query): Builder { return $query->where('status', 'confirmed'); }
    public function scopeRejected(Builder $query): Builder { return $query->where('status', 'rejected'); }
    public function scopeCancelled(Builder $query): Builder { return $query->where('status', 'cancelled'); }
    
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['rejected', 'cancelled']);
    }

    public function scopeForLab(Builder $query, string $labName): Builder
    {
        return $query->where('lab_name', $labName);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('booking_date', $date);
    }

    public function scopeForDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('booking_date', Carbon::today('Asia/Jakarta'));
    }

    public function scopeFuture(Builder $query): Builder
    {
        return $query->whereDate('booking_date', '>=', Carbon::today('Asia/Jakarta'));
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('end_date', '<', Carbon::today('Asia/Jakarta'));
    }

    public function scopeAwaitingApprovalBy(Builder $query, string $role): Builder
    {
        return match($role) {
            'dosen' => $query->where('status', 'pending')
                ->whereHas('user', fn($q) => $q->where('role', 'mahasiswa')),
            'teknisi' => $query->where('status', 'approved_dosen')
                ->orWhere(function($q) {
                    $q->where('status', 'pending')
                      ->whereHas('user', fn($u) => $u->where('role', 'dosen'));
                }),
            'ketua_lab' => $query->where('status', 'approved_teknisi'),
            default => $query,
        };
    }

    public function scopeGroup(Builder $query): Builder
    {
        return $query->where('is_group', true)
            ->whereNotNull('members')
            ->whereJsonLength('members', '>', 0);
    }

    public function scopeIndividual(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->where('is_group', false)
              ->orWhereNull('members')
              ->orWhereJsonLength('members', '=', 0);
        });
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('activity', 'like', "%{$keyword}%")
              ->orWhere('purpose', 'like', "%{$keyword}%")
              ->orWhere('lab_name', 'like', "%{$keyword}%")
              ->orWhere('session', 'like', "%{$keyword}%")
              ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('nim', 'like', "%{$keyword}%")
                  ->orWhere('nip', 'like', "%{$keyword}%"));
        });
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByDateAsc(Builder $query): Builder
    {
        return $query->orderBy('booking_date', 'asc')->orderBy('start_time', 'asc');
    }

    // ========================================================================
    // STATIC HELPERS
    // ========================================================================

    public static function countPendingForUser(int $userId): int
    {
        return static::where('user_id', $userId)->where('status', 'pending')->count();
    }

    public static function countConfirmedForUser(int $userId): int
    {
        return static::where('user_id', $userId)->where('status', 'confirmed')->count();
    }

    public static function countAwaitingApproval(string $role): int
    {
        return match($role) {
            'dosen' => static::where('status', 'pending')
                ->whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
                ->count(),
            'teknisi' => static::where('status', 'approved_dosen')->count()
                + static::where('status', 'pending')
                    ->whereHas('user', fn($q) => $q->where('role', 'dosen'))
                    ->count(),
            'ketua_lab' => static::where('status', 'approved_teknisi')->count(),
            default => 0,
        };
    }

    public static function isLabBooked(string $labName, string $date, string $startTime, string $endTime): bool
    {
        return static::where('lab_name', $labName)
            ->whereDate('booking_date', $date)
            ->where('status', 'confirmed')
            ->where(function($q) use ($startTime, $endTime) {
                $q->where(function($sub) use ($startTime, $endTime) {
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }

    // ========================================================================
    // BOOT METHOD
    // ========================================================================

    protected static function boot(): void
    {
        parent::boot();

        // ✅ Auto-set default values on creating
        static::creating(function ($booking) {
            // Auto-set prodi to default
            if (!$booking->prodi) {
                $booking->prodi = 'Teknik Informatika';
            }
            
            // Auto-set golongan from user if not provided
            if (!$booking->golongan && $booking->user?->golongan) {
                $booking->golongan = $booking->user->golongan;
            }
            
            // Auto-set phone from user if not provided
            if (!$booking->phone && $booking->user?->phone) {
                $booking->phone = $booking->user->phone;
            }
        });

        // ✅ Log status changes for audit
        static::updated(function ($booking) {
            if ($booking->isDirty('status')) {
                Log::info('Booking status changed', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'role' => $booking->user?->role,
                    'old_status' => $booking->getOriginal('status'),
                    'new_status' => $booking->status,
                    'updated_by' => auth()->id(),
                ]);
            }
        });
    }
}