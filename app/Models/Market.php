<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 *
 * Class Player
 * @package App\Market
 * @property integer id
 * @property integer from
 * @property integer to
 * @property integer player_id
 * @property double price
 * @property Player player
 * @property Team team
 * @method static static first(int $id, array $ar = [])
 * @method static static find(int $id, array $ar = [])
 * @method static int count(string $str = "*")
 * @method static static firstOrFail(array $ar = [])
 * @method static static findOrFail(int $id, array $ar = [])
 * @method static \Illuminate\Database\Eloquent\Builder where(string $string, string $string1, string $string2 = "")
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn(string $string, array $arr)
 * @method static static create(array $array)
 */
class Market extends Model
{
    use HasFactory;


    protected $fillable = [
        "buyer_id",
        "player_id",
        "price",
        "seller_id",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'buyer_id',
        'player_id',
        'seller_id',
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
       return $this->belongsTo(Team::class, "seller_id");
    }


    public function newTeam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class, "buyer_id");
    }


    public function player(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return   $this->belongsTo(Player::class);
    }
}
