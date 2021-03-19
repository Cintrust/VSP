<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'team_id' => Team::factory(),
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "country" => $this->faker->country,
            "position" => $this->faker->randomElement(
                ["goalkeeper", "goalkeeper", "goalkeeper",
                    "defender", "defender", "defender", "defender", "defender", "defender",
                    "midfielder", "midfielder", "midfielder", "midfielder", "midfielder", "midfielder",
                    "attacker", "attacker", "attacker", "attacker", "attacker",
                ]
            ),
            "age" => $this->faker->numberBetween(18, 40),
            "market_value" => $this->faker->numberBetween(1000000, 4000000),
        ];
    }
}
