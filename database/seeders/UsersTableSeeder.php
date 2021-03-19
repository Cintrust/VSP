<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param bool $seed_all
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create()->unique();


        User::create([
            "name"=>$faker->name,
            "email"=>"admin@admin.com",
            "password"=>Hash::make("1234567890"),
            "role_id"=>User::NEWBIE|User::ADMIN
        ]);



        if (!func_num_args()){
            return;
        }

        $users = [];
        for ($i=0;$i<30;++$i){
            $users[]= [
                "name"=>$faker->name,
                "email"=>"user_$i@user.com",
                "password"=>Hash::make("1234567890"),
                "created_at"=>now(),
                "updated_at"=>now(),
            ];

        }
        User::query()->insertOrIgnore($users);
    }
}
