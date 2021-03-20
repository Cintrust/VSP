<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;

class PlayersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $teams = Team::all();

        $positions = ["goalkeeper", "goalkeeper", "goalkeeper",
            "defender", "defender", "defender", "defender", "defender", "defender",
            "midfielder", "midfielder", "midfielder", "midfielder", "midfielder", "midfielder",
            "attacker", "attacker", "attacker", "attacker", "attacker",
        ];
        $players = [];
        foreach ($teams as $team) {

            foreach ($positions as $position) {
                $players[] = [
                    "team_id" => $team->id,
                    "first_name" => $faker->firstName,
                    "last_name" => $faker->lastName,
                    "position" => $position,
                    "country" => $faker->country,
                    "age" => $faker->numberBetween(18, 40),
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }

        }

        Player::query()->insertOrIgnore($players);
    }
}
