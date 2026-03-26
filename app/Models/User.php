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

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_kalab', 'nim', 'nip',
        'golongan', 'prodi', 'phone', 'lab_name',
        'google_id', 'avatar', 'google_token', 'google_refresh_token', 'google_connected_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google_token', 'google_refresh_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'google_connected_at' => 'datetime',
        'is_kalab' => 'boolean',
    ];

    public function bookings(): HasMany { return $this->hasMany(Booking::class, 'user_id'); }
    public function approvedBookingsAsDosen(): HasMany { return $this->hasMany(Booking::class, 'approved_by_dosen'); }
    public function approvedBookingsAsTeknisi(): HasMany { return $this->hasMany(Booking::class, 'approved_by_teknisi'); }
    public function approvedBookingsAsKalab(): HasMany { return $this->hasMany(Booking::class, 'approved_by_kalab'); }
    public function supervisedBookings(): HasMany { return $this->hasMany(Booking::class, 'supervisor_id'); }

    public function isMahasiswa(): bool { return $this->role === 'mahasiswa' || str_ends_with($this->email, '@student.polije.ac.id'); }
    public function isDosen(): bool { return $this->role === 'dosen' || (str_ends_with($this->email, '@polije.ac.id') && $this->role === 'staff'); }
    public function isKalab(): bool { return $this->isDosen() && (bool) $this->is_kalab; }
    public function isKetuaLab(): bool { return $this->role === 'ketua_lab' || $this->isKalab(); }
    public function isTeknisi(): bool { return $this->role === 'teknisi'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isStaff(): bool { return in_array($this->role, ['dosen','ketua_lab','teknisi','admin']) || str_ends_with($this->email, '@polije.ac.id'); }

    public function getIdentifierAttribute(): ?string { return $this->isMahasiswa() ? $this->nim : ($this->isDosen() ? $this->nip : null); }

    public function getFormattedPhoneAttribute(): ?string {
        if (!$this->phone) return null;
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        return (strlen($phone) >= 11 && substr($phone, 0, 2) === '08') ? '+62'.substr($phone, 1) : $this->phone;
    }

    public function getDisplayNameAttribute(): string {
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

    public function getInitialAttribute(): string { return strtoupper(substr($this->name, 0, 1)); }

    public function isPolijeDomain(): bool {
        $domains = explode(',', env('GOOGLE_ALLOWED_DOMAINS', 'student.polije.ac.id,polije.ac.id'));
        return in_array(substr(strrchr($this->email, "@"), 1), $domains);
    }
    public function isGoogleUser(): bool { return !empty($this->google_id); }
    public function hasGoogleConnected(): bool { return $this->isGoogleUser() && !empty($this->google_refresh_token); }

    public function canCreateBooking(): bool { return in_array($this->role, ['mahasiswa','dosen','teknisi','ketua_lab','admin']); }
    public function canApproveBookings(): bool { return $this->isAdmin() || $this->isTeknisi() || $this->isKetuaLab() || $this->isKalab(); }
    public function canViewAllBookings(): bool { return $this->isAdmin() || $this->isTeknisi() || $this->isKetuaLab() || $this->isKalab(); }
    public function canManageLabs(): bool { return $this->role === 'admin'; }
    public function canManageUsers(): bool { return $this->role === 'admin'; }
    public function canApproveAsKalab(): bool { return $this->isKalab() || $this->role === 'ketua_lab' || $this->isAdmin(); }

    public function scopeMahasiswa(Builder $q): Builder { return $q->where('role','mahasiswa')->orWhere('email','like','%@student.polije.ac.id'); }
    public function scopeDosen(Builder $q): Builder { return $q->where('role','dosen')->orWhere(fn($x) => $x->where('role','staff')->where('email','like','%@polije.ac.id')); }
    public function scopeKalab(Builder $q): Builder { return $q->where('role','dosen')->where('is_kalab', true); }
    public function scopeStaff(Builder $q): Builder { return $q->whereIn('role',['dosen','ketua_lab','teknisi','admin'])->orWhere('email','like','%@polije.ac.id'); }
    public function scopeSearch(Builder $q, string $k): Builder { return $q->where(fn($x) => $x->where('name','like',"%{$k}%")->orWhere('email','like',"%{$k}%")->orWhere('nim','like',"%{$k}%")->orWhere('nip','like',"%{$k}%")); }

    public static function getActiveKalab(): ?self { return self::kalab()->first(); }

    public static function transferKalab(int $id): bool {
        $u = self::find($id);
        if (!$u || !$u->isDosen()) return false;
        self::where('is_kalab', true)->update(['is_kalab' => false]);
        return $u->update(['is_kalab' => true]);
    }

    protected static function boot(): void {
        parent::boot();
        static::creating(fn($u) => $u->prodi ??= $u->isMahasiswa() ? 'Teknik Informatika' : null);
        static::creating(fn($u) => $u->is_kalab ??= false);
        static::updating(fn($u) => $u->role !== 'dosen' && $u->isDirty('role') ? $u->is_kalab = false : null);
        static::created(fn($u) => Log::info('User created', ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role,'is_kalab'=>$u->is_kalab]));
    }
}
