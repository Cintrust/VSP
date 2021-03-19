<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 *
 * Class User
 * @package App\Team
 * @property integer id
 * @property integer user_id
 * @property string country
 * @property double budget
 * @property string name
 * @property User user
 *  @property Player[]|\Illuminate\Database\Eloquent\Collection players
 * @method static static first(int $id,array $ar = [])
 * @method static static find(int $id,array $ar = [])
 * @method static int count(string $str = "*")
 * @method static static firstOrFail( array $ar = [])
 * @method static static findOrFail(int $id, array $ar = [])
 * @method static \Illuminate\Database\Eloquent\Builder where(string $string, string $string1, string $string2 = "")
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn(string $string, array $arr)
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $string, array $arr)
 * @method static static create(array $array)
 */
class Team extends Model
{

    use HasFactory;

    protected $fillable =[
        "user_id",
        "country",
        "budget",
        "name",
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['team_value'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function players(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function getTeamValueAttribute(): int
    {
        return  $this->players()->sum("market_value");
    }

}
