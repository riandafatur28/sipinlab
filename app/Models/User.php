<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Auth fields
        'name',
        'email',
        'password',

        // Role & Identity
        'role',
        'is_kalab',
        'nim',
        'nip',
        'golongan',
        'prodi',

        // Contact
        'phone',

        // Google Auth fields
        'google_id',
        'avatar',
        'google_token',
        'google_refresh_token',
        'google_connected_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'google_connected_at' => 'datetime',
        'is_kalab' => 'boolean',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function approvedBookingsAsDosen(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_dosen');
    }

    public function approvedBookingsAsTeknisi(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_teknisi');
    }

    public function approvedBookingsAsKalab(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_kalab');
    }

    public function supervisedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'supervisor_id');
    }

    // ========================================================================
    // ROLE CHECKERS
    // ========================================================================

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa' ||
               str_ends_with($this->email, '@student.polije.ac.id');
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen' ||
               (str_ends_with($this->email, '@polije.ac.id') && $this->role === 'staff');
    }

    /**
     * Check if user is currently active Kalab.
     * Hanya dosen dengan flag is_kalab = true.
     */
    public function isKalab(): bool
    {
        return $this->isDosen() && $this->is_kalab;
    }

    /**
     * Check if user is Ketua Lab (Kalab).
     * Support backward compatibility dengan role 'ketua_lab'.
     */
    public function isKetuaLab(): bool
    {
        return $this->role === 'ketua_lab' || $this->isKalab();
    }

    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['dosen', 'ketua_lab', 'teknisi', 'admin']) ||
               str_ends_with($this->email, '@polije.ac.id');
    }

    // ========================================================================
    // IDENTITY & CONTACT HELPERS
    // ========================================================================

    public function getIdentifierAttribute(): ?string
    {
        if ($this->isMahasiswa()) {
            return $this->nim;
        }
        if ($this->isDosen()) {
            return $this->nip;
        }
        return null;
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($phone) >= 11 && substr($phone, 0, 2) === '08') {
            return '+62' . substr($phone, 1);
        }
        return $this->phone;
    }

    public function getDisplayNameAttribute(): string
    {
        $badge = match(true) {
            $this->isKalab() => '👔',
            $this->role === 'mahasiswa' => '🎓',
            $this->role === 'dosen' => '👨‍🏫',
            $this->role === 'ketua_lab' => '👔',
            $this->role === 'teknisi' => '🔧',
            $this->role === 'admin' => '⚙️',
            default => '',
        };
        return "{$badge} {$this->name}";
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    // ========================================================================
    // GOOGLE AUTH HELPERS
    // ========================================================================

    public function isPolijeDomain(): bool
    {
        $allowedDomains = explode(',', env('GOOGLE_ALLOWED_DOMAINS', 'student.polije.ac.id,polije.ac.id'));
        $domain = substr(strrchr($this->email, "@"), 1);
        return in_array($domain, $allowedDomains);
    }

    public function isGoogleUser(): bool
    {
        return !empty($this->google_id);
    }

    public function hasGoogleConnected(): bool
    {
        return $this->isGoogleUser() && !empty($this->google_refresh_token);
    }

    // ========================================================================
    // PERMISSION & CAPABILITY CHECKERS
    // ========================================================================

    public function canCreateBooking(): bool
    {
        return in_array($this->role, ['mahasiswa', 'dosen', 'teknisi', 'ketua_lab', 'admin']);
    }

    public function canApproveBookings(): bool
    {
        return $this->isAdmin() ||
               $this->isTeknisi() ||
               $this->isKetuaLab() ||
               $this->isKalab();
    }

    public function canViewAllBookings(): bool
    {
        return $this->isAdmin() ||
               $this->isTeknisi() ||
               $this->isKetuaLab() ||
               $this->isKalab();
    }

    public function canManageLabs(): bool
    {
        return $this->role === 'admin';
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'admin';
    }

    public function canApproveAsKalab(): bool
    {
        return $this->isKalab() || $this->role === 'ketua_lab' || $this->isAdmin();
    }

    // ========================================================================
    // SCOPES FOR QUERIES
    // ========================================================================

    public function scopeMahasiswa(Builder $query): Builder
    {
        return $query->where('role', 'mahasiswa')
            ->orWhere('email', 'like', '%@student.polije.ac.id');
    }

    public function scopeDosen(Builder $query): Builder
    {
        return $query->where('role', 'dosen')
            ->orWhere(function($q) {
                $q->where('role', 'staff')
                  ->where('email', 'like', '%@polije.ac.id');
            });
    }

    public function scopeKalab(Builder $query): Builder
    {
        return $query->where('role', 'dosen')
                     ->where('is_kalab', true);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereIn('role', ['dosen', 'ketua_lab', 'teknisi', 'admin'])
            ->orWhere('email', 'like', '%@polije.ac.id');
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%")
              ->orWhere('nim', 'like', "%{$keyword}%")
              ->orWhere('nip', 'like', "%{$keyword}%");
        });
    }

    // ========================================================================
    // STATIC HELPERS
    // ========================================================================

    public static function getActiveKalab(): ?self
    {
        return self::kalab()->first();
    }

    public static function transferKalab(int $newKalabUserId): bool
    {
        $newKalab = self::find($newKalabUserId);

        if (!$newKalab || !$newKalab->isDosen()) {
            return false;
        }

        // Copot kalab lama
        self::where('is_kalab', true)->update(['is_kalab' => false]);

        // Angkat kalab baru
        $newKalab->update(['is_kalab' => true]);

        return true;
    }

    // ========================================================================
    // BOOT METHOD
    // ========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            if (!$user->prodi && $user->isMahasiswa()) {
                $user->prodi = 'Teknik Informatika';
            }

            if (!$user->is_kalab) {
                $user->is_kalab = false;
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('role') && $user->role !== 'dosen' && $user->is_kalab) {
                $user->is_kalab = false;
            }
        });

        static::created(function ($user) {
            Log::info('User created', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_kalab' => $user->is_kalab,
            ]);
        });
    }
}
