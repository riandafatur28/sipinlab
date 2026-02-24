<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'nim',           // ✅ Nomor Induk Mahasiswa (untuk mahasiswa)
        'nip',           // ✅ Nomor Induk Pegawai (untuk dosen)
        'golongan',      // ✅ Golongan praktikum (A, B, C)
        'prodi',         // ✅ Program Studi (default: Teknik Informatika)
        
        // Contact
        'phone',         // ✅ No. Telepon / WhatsApp
        
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
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get all bookings made by this user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Get bookings approved by this user as Dosen.
     */
    public function approvedBookingsAsDosen(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_dosen');
    }

    /**
     * Get bookings approved by this user as Teknisi.
     */
    public function approvedBookingsAsTeknisi(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_teknisi');
    }

    /**
     * Get bookings approved by this user as Ka Lab.
     */
    public function approvedBookingsAsKalab(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by_kalab');
    }

    /**
     * Get bookings where this user is supervisor.
     */
    public function supervisedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'supervisor_id');
    }

    // ========================================================================
    // ROLE CHECKERS
    // ========================================================================

    /**
     * Check if user is mahasiswa.
     */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa' ||
               str_ends_with($this->email, '@student.polije.ac.id');
    }

    /**
     * Check if user is dosen.
     */
    public function isDosen(): bool
    {
        return $this->role === 'dosen' ||
               (str_ends_with($this->email, '@polije.ac.id') && $this->role === 'staff');
    }

    /**
     * Check if user is ketua lab.
     */
    public function isKetuaLab(): bool
    {
        return $this->role === 'ketua_lab';
    }

    /**
     * Check if user is teknisi.
     */
    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff (dosen, ketua lab, atau teknisi).
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['dosen', 'ketua_lab', 'teknisi', 'admin']) ||
               str_ends_with($this->email, '@polije.ac.id');
    }

    // ========================================================================
    // IDENTITY & CONTACT HELPERS
    // ========================================================================

    /**
     * Get user identifier (NIM for mahasiswa, NIP for dosen).
     */
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

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }
        // Format Indonesian phone number
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($phone) >= 11 && substr($phone, 0, 2) === '08') {
            return '+62' . substr($phone, 1);
        }
        return $this->phone;
    }

    /**
     * Get user display name with role badge.
     */
    public function getDisplayNameAttribute(): string
    {
        $badge = match($this->role) {
            'mahasiswa' => '🎓',
            'dosen' => '👨‍🏫',
            'ketua_lab' => '👔',
            'teknisi' => '🔧',
            'admin' => '⚙️',
            default => '',
        };
        return "{$badge} {$this->name}";
    }

    /**
     * Get user initial for avatar.
     */
    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    // ========================================================================
    // GOOGLE AUTH HELPERS
    // ========================================================================

    /**
     * Check if user is from Polije domain.
     */
    public function isPolijeDomain(): bool
    {
        $allowedDomains = explode(',', env('GOOGLE_ALLOWED_DOMAINS', 'student.polije.ac.id,polije.ac.id'));
        $domain = substr(strrchr($this->email, "@"), 1);
        return in_array($domain, $allowedDomains);
    }

    /**
     * Check if user logged in via Google.
     */
    public function isGoogleUser(): bool
    {
        return !empty($this->google_id);
    }

    /**
     * Check if user has connected Google account.
     */
    public function hasGoogleConnected(): bool
    {
        return $this->isGoogleUser() && !empty($this->google_refresh_token);
    }

    // ========================================================================
    // PERMISSION & CAPABILITY CHECKERS
    // ========================================================================

    /**
     * Check if user can create booking.
     */
    public function canCreateBooking(): bool
    {
        return in_array($this->role, ['mahasiswa', 'dosen', 'teknisi', 'ketua_lab', 'admin']);
    }

    /**
     * Check if user can approve bookings.
     */
    public function canApproveBookings(): bool
    {
        return in_array($this->role, ['dosen', 'teknisi', 'ketua_lab', 'admin']);
    }

    /**
     * Check if user can view all bookings.
     */
    public function canViewAllBookings(): bool
    {
        return in_array($this->role, ['teknisi', 'ketua_lab', 'admin']);
    }

    /**
     * Check if user can manage labs.
     */
    public function canManageLabs(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->role === 'admin';
    }

    // ========================================================================
    // SCOPES FOR QUERIES
    // ========================================================================

    /**
     * Scope a query to only include mahasiswa.
     */
    public function scopeMahasiswa($query)
    {
        return $query->where('role', 'mahasiswa')
            ->orWhere('email', 'like', '%@student.polije.ac.id');
    }

    /**
     * Scope a query to only include dosen.
     */
    public function scopeDosen($query)
    {
        return $query->where('role', 'dosen')
            ->orWhere(function($q) {
                $q->where('role', 'staff')
                  ->where('email', 'like', '%@polije.ac.id');
            });
    }

    /**
     * Scope a query to only include staff.
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['dosen', 'ketua_lab', 'teknisi', 'admin'])
            ->orWhere('email', 'like', '%@polije.ac.id');
    }

    /**
     * Scope a query to search users by name, email, NIM, or NIP.
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%")
              ->orWhere('nim', 'like', "%{$keyword}%")
              ->orWhere('nip', 'like', "%{$keyword}%");
        });
    }

    // ========================================================================
    // BOOT METHOD
    // ========================================================================

    /**
     * Boot the model and attach event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-set default prodi for new users
        static::creating(function ($user) {
            if (!$user->prodi && $user->isMahasiswa()) {
                $user->prodi = 'Teknik Informatika';
            }
        });

        // Log user creation for audit
        static::created(function ($user) {
            \Log::info('User created', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        });
    }
}