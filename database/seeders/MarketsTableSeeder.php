<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;

class MarketsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = Team::query()->inRandomOrder()->limit(5)->get(["id", "budget"]);

        /** @var Player[] $players */
        $players = Player::query()->inRandomOrder()->limit(100)
            ->whereNotIn("team_id", $teams->pluck("id"))->get();
        $faker = \Faker\Factory::create();

        $markets = [];

        foreach ($players as $player) {
//            get a random team
            $new_team = $teams->random();
            $price = $faker->numberBetween($min = 1000000, $max = 5000000);

//            check if team can afford player
            if ($new_team->budget > $price) {

                $markets[] = [
                    "player_id" => $player->id,
                    "seller_id" => $player->team_id,
                    "buyer_id" => $new_team->id,
                    "price" => $price,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];

//                increase players value by random % between 10 and 100
                $player_new_market_value = $price * $faker->randomFloat($nbMaxDecimals = 2, $min = 1.1, $max = 2);

//                adjust the old team budget
                $player->team()->increment("budget", $price);

//                update player market value and team
                $player->update(["market_value" => $player_new_market_value, "team_id" => $new_team->id]);

//                adjust new team budget
                $new_team->budget -= $price;


            } else {
                $markets[] = [
                    "player_id" => $player->id,
                    "seller_id" => $player->team_id,
                    "buyer_id" => null,
                    "price" => $price,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }


        }

        Market::query()->insert($markets);

//        save changes to db
        foreach ($teams as $team) {
            $team->saveOrFail();
        }

    }
}
