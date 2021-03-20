<?php

namespace App\Http\Requests;

use App\Rules\CheckIfPlayerCanBeAddedToMarket;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreMarketApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "price" => "bail|required|integer|min:0",
            "player_id" => [
                "bail", "required",
                "integer", "min:0",
                "exists:players,id",
                new CheckIfPlayerCanBeAddedToMarket()
            ],
        ];
    }


}
