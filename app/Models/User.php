<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // mahasiswa, dosen, ketua_lab, teknisi
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is mahasiswa
     */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa' ||
               str_ends_with($this->email, '@student.polije.ac.id');
    }

    /**
     * Check if user is dosen
     */
    public function isDosen(): bool
    {
        return $this->role === 'dosen' ||
               (str_ends_with($this->email, '@polije.ac.id') && $this->role === 'staff');
    }

    /**
     * Check if user is ketua lab
     */
    public function isKetuaLab(): bool
    {
        return $this->role === 'ketua_lab';
    }

    /**
     * Check if user is teknisi
     */
    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }

    /**
     * Check if user is staff (dosen, ketua lab, atau teknisi)
     */
    public function isStaff(): bool
    {
        return str_ends_with($this->email, '@polije.ac.id');
    }
}
