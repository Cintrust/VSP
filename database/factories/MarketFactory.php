<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Market::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'player_id' => Player::factory(),

            'seller_id' => function (array $attributes) {
                return Player::find($attributes['player_id'])->team_id;
            },
            "price" => $this->faker->numberBetween(1000000, 4000000),
        ];
    }

    public function buyer()
    {
        return $this->state(function (array $attributes) {
            return [
                'buyer_id' => Team::where("id", "<>", $attributes["seller_id"])
                    ->inRandomOrder()->value("id")
            ];
        });
    }
}
