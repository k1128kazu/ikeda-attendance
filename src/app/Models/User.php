<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    public function approvedCorrectionRequests()
    {
        return $this->hasMany(
            AttendanceCorrectionRequest::class,
            'approved_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }
}
