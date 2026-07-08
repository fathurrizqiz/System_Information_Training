<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     *
     */
    protected $fillable = [
        'name',
        'nrp',
        'employee_id',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     *
     */


    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'password' => 'hashed',
        'role' => 'array',
    ];

    /**
     * Override the default authentication username to use 'nrp' instead of 'email'
     */
    public function getAuthIdentifierName(): string
    {
        return 'nrp';
    }
}
