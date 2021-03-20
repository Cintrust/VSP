<?php
/**
 * Created by PhpStorm
 * User: Junior Trust
 * Date: 2/26/2021
 * Time: 5:46 PM
 */

namespace App\Http\Controllers;


use App\Models\Player;

trait GeneratePlayers
{

    public $positions = ["goalkeeper", "goalkeeper", "goalkeeper",
        "defender", "defender", "defender", "defender", "defender", "defender",
        "midfielder", "midfielder", "midfielder", "midfielder", "midfielder", "midfielder",
        "attacker", "attacker", "attacker", "attacker", "attacker",
    ];

    public function generatePlayers($team_id)
    {

        $faker = \Faker\Factory::create();
        $players = [];
        foreach ($this->positions as $position) {
            $players[] = [
                "team_id" => $team_id,
                "first_name" => $faker->firstName,
                "last_name" => $faker->lastName,
                "country" => $faker->country,
                "position" => $position,
                "age" => $faker->numberBetween(18, 40),
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

//      insert all players at a go
        Player::query()->insert($players);
    }

}
