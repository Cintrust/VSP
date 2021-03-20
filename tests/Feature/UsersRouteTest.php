<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersRouteTest extends TestCase
{
    use WithFaker,RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_users()
    {
//        Unauthenticated User
        $response = $this->get(route("get_all_users"));
        $response->assertStatus(401);

        //        authenticate User
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->get(route("get_all_users"));

        $response->assertStatus(403);

        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $response = $this->get(route("get_all_users"));

        $response->assertOk();
    }

    public function test_create_user()
    {
//        create user with invalid details
        $response = $this->post(route("create_user"), [
//            "name" => null,
            "email" => null,
            "password" => $this->faker->password(2, 5),
            "team_name" => "",
            "team_country" => "  ",
        ]);


        $response->assertStatus(422)->assertJson([
            "message" => true,
            "errors" => [
                "email" => true,
                "name" => true,
                "password" => true,
                "team_name" => true,
                "team_country" => true,
            ],
        ]);


        //        create user with valid details
        $prefix = $this->faker->randomNumber();
        $data = [
            "name" => $this->faker->name,
            "email" => $prefix . "_" . $this->faker->unique()->safeEmail,
            "password" => $this->faker->password(8),
            "team_name" => $prefix . "_" . $this->faker->company,
            "team_country" => $this->faker->country,
        ];
        $response = $this->post(route("create_user"), $data);


        $response->assertCreated()
            ->assertJsonStructure([
                "name", "email", "id",
                "team" => [
                    "name", "user_id", "country", "name", "id", "team_value",
                    "players" => [
                        "*" => [
                            "id", "team_id", "first_name", "last_name", "country", "age",
                            "market_value"
                        ]
                    ]
                ]
            ])->assertJsonFragment([
                "name" => $data["name"],
                "email" => $data["email"],
            ])->assertJsonPath("team.name", $data["team_name"])
            ->assertJsonPath("team.country", $data["team_country"])
            ->assertJsonCount(20, "team.players")->json();

//        try authenticating created user
        $response = $this->post(route("login"),
            ["email" => $data["email"], "password" => $data["password"]]
        );

        $response->assertStatus(200)->assertJson([
            'token' => true,
        ]);


//        try creating user with existing details
        $response = $this->post(route("create_user"), $data);


        $response->assertStatus(422)->assertJsonValidationErrors([
            "email" => "The email has already been taken.",
            "team_name" => "The team name has already been taken."
        ]);
    }

    public function test_get_user()
    {
//        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();

        //        Unauthenticated User
        $response = $this->get(route("get_user", $user->id));
        $response->assertStatus(401);

        //        authenticate User
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->get(route("get_user", $user->id));

        $response->assertStatus(403);


        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

//        get existing user
        $response = $this->get(route("get_user", $user->id));

        $response->assertOk()->assertExactJson($user->toArray());

//        get existing user with resource
        $response = $this->get(route("get_user", ["user" => $user->id, "with_resource" => true]));

        $user->load("team.players");
        $response->assertOk()->assertExactJson($user->toArray());

        $max_id = User::query()->max("id") + 1;
        //        get non existing user
        $response = $this->get(route("get_user", $max_id));

        $response->assertNotFound();


    }

    public function test_update_user()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->create();

        $data = [
            "name" => $this->faker->name,
            "password" => $this->faker->password(8),
            "email" => $this->faker->randomNumber() . "_" . $this->faker->unique()->safeEmail,
        ];
        //        Unauthenticated User
        $response = $this->patch(route("update_user", $user->id), $data);
        $response->assertStatus(401);


        //        authenticate User
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->patch(route("update_user", $user->id), $data);

        $response->assertStatus(403);


        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $response = $this->patch(route("update_user", $user->id), $data);
        $response->assertOk()->assertExactJson(["message" => "User Details Updated"]);

        $user->refresh();

//        check if details were changed
        $this->assertSame($user->name, $data['name']);
        $this->assertSame($user->email, $data['email']);
        $this->assertTrue(Hash::check($data['password'], $user->password));

//        try updating user with wrong details
        $response = $this->patch(route("update_user", $user->id), [
            "email" => $data["email"],// try using the same email
            "name" => "P",// try using one letter
            "password" => "Pass",// try using 4 letters
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            "name","email","password"
        ]);


        $max_id = User::query()->max("id") + 1;
        //        edit non existing user
        $response = $this->patch(route("update_user", $max_id),$data);

        $response->assertNotFound();
    }

    public function test_delete_user()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();
        $user->load("team.players");


        //        Unauthenticated User
        $response = $this->delete(route("delete_user", $user->id));
        $response->assertStatus(401);


        //        authenticate User
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->delete(route("delete_user", $user->id));

        $response->assertStatus(403);

        //        authenticate User as admin
        /** @var User $admin */
        $admin = Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $response = $this->delete(route("delete_user", $user->id));

        $response->assertStatus(204);

//        test if data is actually deleted
        $this->assertFalse(Player::whereIn("id",$user->players->pluck("id")->toArray())->exists());
        $this->assertFalse(Team::where("id",$user->team->id)->exists());
        $this->assertFalse(User::where("id",$user->id)->exists());

//        try deleting admin with same admin
        $response = $this->delete(route("delete_user", $admin->id));

        $response->assertStatus(403);

        $max_id = User::query()->max("id") + 1;

//        delete non existing user
        $response = $this->delete(route("delete_user", $max_id));

        $response->assertStatus(404);

    }
}
