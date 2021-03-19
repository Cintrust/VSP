<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $users = User::all();

        $teams =[];
        foreach ($users as $user) {
            $teams[]= [
                "user_id"=>$user->id,
                "country"=>$faker->country,
                "name"=>$faker->name." FC",
                "created_at"=>now(),
                "updated_at"=>now(),
            ];

        }

        Team::query()->insertOrIgnore($teams);

    }
}
