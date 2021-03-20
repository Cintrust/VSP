<?php

namespace App\Rules;

use App\Models\Player;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CheckIfPlayerCanBeAddedToMarket implements Rule
{

    protected $msg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $player = Player::findOrFail($value);

        if (!Auth::user()->isAdmin() && $player->user()->where("users.id", Auth::id())->doesntExist()) {
//logged in user is not admin and wants to add a player
// that is not on his team to the market
            $this->msg = "This player is not on your team";
            return false;
        } elseif ($player->market()->exists()) {
//           player is already on the market
            $this->msg = "This player is already on the market";
            return false;
        }

        return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->msg;
    }
}
