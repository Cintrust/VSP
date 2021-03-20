<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 *
 * Class User
 * @package App\Models
 * @property integer id
 * @property integer role_id
 * @property string name
 * @property string email
 * @property string password
 * @property Player[]|\Illuminate\Database\Eloquent\Collection players
 * @property Team team
 *
 * @method static User first(int $id, array $ar = [])
 * @method static User find(int $id, array $ar = [])
 * @method static int count(string $str = "*")
 * @method static User firstOrFail(array $ar = [])
 * @method static User findOrFail(int $id, array $ar = [])
 * @method static \Illuminate\Database\Eloquent\Builder where(string $string, string $string1, string $string2 = "")
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn(string $string, array $arr)
 * @method static static create(array $array)
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
 */
class User extends Authenticatable
{
    use  HasApiTokens, HasFactory, Notifiable;


    /*user roles*/
    public const NEWBIE = 1 << 0;
    public const ADMIN = 1 << 1;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role_id',
        "laravel_through_key"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function isAdmin(): int
    {
        return $this->role_id & self::ADMIN;
    }

    public function team(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Team::class);
    }

    public function players(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Player::class, Team::class);
    }
}
