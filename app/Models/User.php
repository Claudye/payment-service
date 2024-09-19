<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\FilterByRequest;
use App\Traits\Payable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $profession
 * @property string $phone
 * @property string $photo
 * @property string $email_verified_at
 * @property string $bio
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Wallet $wallet

 * @property Collection $unreadNotifications
 * @property Setting|null $setting
 *
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, FilterByRequest;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
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
}
