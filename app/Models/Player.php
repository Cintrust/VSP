<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 *
 * Class Player
 * @package App\Team
 * @property integer id
 * @property integer team_id
 * @property integer age
 * @property double market_value
 * @property string first_name
 * @property string last_name
 * @property string country
 * @property string position
 * @property User $user
 * @property Market $market
 *
 * @method static static first(int $id, array $ar = [])
 * @method static static find(int $id, array $ar = [])
 * @method static int count(string $str = "*")
 * @method static static firstOrFail(array $ar = [])
 * @method static static findOrFail(int $id, array $ar = [])
 * @method static \Illuminate\Database\Eloquent\Builder where(string $string, string $string1, string $string2 = "")
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn(string $string, array $arr)
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $string, array $arr)
 * @method static static create(array $array)
 */
class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        "team_id",
        "first_name",
        "last_name",
        "country",
        "age",
        "position",
        "market_value",
    ];

    protected $hidden =[
        "laravel_through_key"
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
       return $this->hasOneThrough(User::class, Team::class,
            "id", "id",
            "team_id", "user_id");
    }

    public function market(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Market::class)->whereNull("buyer_id");
    }
}
