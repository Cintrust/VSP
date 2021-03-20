<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamsRouteTest extends TestCase
{
    use WithFaker,RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_teams()
    {
        $this->checkUnauthorisedReq("get", route("get_all_teams"));

        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $response = $this->get(route("get_all_teams"));

        $response->assertOk();

    }

    protected function checkUnauthorisedReq($method, ...$args)
    {
        //        Unauthenticated User
        $response = $this->{$method}(...$args);
        $response->assertStatus(401);

        //        authenticate User
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->{$method}(...$args);

        $response->assertStatus(403);
    }

    public function test_create_team()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $arr = [
            "user_id" => $user->id,
            "name" => $this->faker->randomNumber() . "_" . $this->faker->name,
            "country" => $this->faker->country,
        ];
        $response = $this->post(route("create_team"), $arr);
        $response->assertStatus(401);

//         authenticate user
        /** @var User $auth */
        $auth = Sanctum::actingAs(
            User::factory()->create(),
        );

        // try create team with wrong details
        $response = $this->post(route("create_team"), [
            "user_id" => $auth->id,
            "name" => "  ",
            "country" => null,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'country']);

        // try create team
        $response = $this->post(route("create_team"), [
            "user_id" => $auth->id,
            "name" => $this->faker->randomNumber() . "_" . $this->faker->name,
            "country" => $this->faker->country,
        ]);
        $auth->load("team.players");

        $response->assertCreated()->assertExactJson($auth->team->toArray());

// try create team for another user
        $response = $this->post(route("create_team"), $arr);
        $response->assertStatus(422)->assertJsonValidationErrors(["user_id" => 'You are not allowed to create team for another user']);


        //authenticate admin
        /** @var User $auth */
        $admin = Sanctum::actingAs(
            User::factory()->admin()->create(),
        );


        //try create team for another user as an admin
        $response = $this->post(route("create_team"), $arr);
        $user->load("team.players");
        $response->assertCreated()->assertExactJson($user->team->toArray());

        //try create team for user that already has a team
        $response = $this->post(route("create_team"), $arr);
        $response->assertStatus(422)->assertJsonValidationErrors(["user_id" => "user already has a team"]);

        $max_id = User::query()->max("id") + 1;


        //try create team for non existing user
        $response = $this->post(route("create_team"), [
            "user_id" => $max_id,
            "name" => $this->faker->randomNumber() . "_" . $this->faker->name,
            "country" => $this->faker->country,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(["user_id"]);

    }

    public function test_get_team()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();

        $user->load("team");

        $response = $this->get(route("get_team",$user->team->id));
        $response->assertStatus(401);

        //         authenticate user
        /** @var User $auth */
        $auth= Sanctum::actingAs(
             User::factory()
                 ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                 ->create(),
        );

//        get team
        $response = $this->get(route("get_team",$auth->team->id));
        $auth->load("team");
        $response->assertOk()->assertExactJson($auth->team->toArray());

        //        get team with resource
        $response = $this->get(route("get_team",["team"=>$auth->team->id,"with_resource"=>true]));
        $auth->load("team.players");
        $response->assertOk()->assertExactJson($auth->team->toArray());

//        get team of another user
        $response = $this->get(route("get_team",$user->team->id));
        $response->assertStatus(403);


        //         authenticate admin
        /** @var User $auth */
        $admin = Sanctum::actingAs(
            User::factory()->admin()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create(),
        );

        //        get team of another user as admin
        $response = $this->get(route("get_team",$user->team->id));
        $response->assertOk()->assertExactJson($user->team->toArray());


        $max_id = Team::query()->max("id") + 1;

        //        get team of non existing team
        $response = $this->get(route("get_team",$max_id));

        $response->assertNotFound();

    }

    public function test_update_team()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();
        $user->load("team");

        $arr = [
            "name" => $this->faker->randomNumber() . "_" .  $this->faker->name,
            "country" => $this->faker->country,
        ];

        $response = $this->patch(route("update_team",$user->team->id), $arr);
        $response->assertStatus(401);


        //         authenticate user
        /** @var User $auth */
        $auth= Sanctum::actingAs(
            User::factory()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create(),
        );

//        update team with invalid details
        $auth->load("team");
        $response = $this->patch(route("update_team",$auth->team->id), [
            "name" => $auth->team->name, // use existing name
            "country" => "   ",
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(["country","name"]);



//        update team
        $auth->load("team");
        $data = [
            "name" => $this->faker->randomNumber() . "_" . $this->faker->name,
            "country" => $this->faker->country,
        ];
        $response = $this->patch(route("update_team",$auth->team->id), $data);

        $response->assertOk()->assertExactJson(["message" => "Team Details Updated"]);
        $auth->refresh();
        $this->assertEquals($auth->team->name,$data['name']);
        $this->assertEquals($auth->team->country,$data['country']);


// update team of another user
        $response = $this->patch(route("update_team",$user->team->id), $data);
        $response->assertStatus(403);


        //         authenticate admin
        /** @var User $auth */
        $admin = Sanctum::actingAs(
            User::factory()->admin()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create(),
        );

    // update team of another user as admin
        $response = $this->patch(route("update_team",$user->team->id), $arr);
        $response->assertOk()->assertExactJson(["message" => "Team Details Updated"]);
        $user->refresh();
        $this->assertEquals($user->team->name,$arr['name']);
        $this->assertEquals($user->team->country,$arr['country']);


        $max_id = Team::query()->max("id") + 1;

        //        update team of non existing user
        $response = $this->patch(route("update_team",$max_id),[
            "name" => $this->faker->randomNumber() . "_" . $this->faker->name,
            "country" => $this->faker->country,
        ]);

        $response->assertNotFound();

    }

    public function test_delete_team()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();
        $user->load("team");

        $this->checkUnauthorisedReq("delete",route("delete_team",$user->team->id));

        //        authenticate User as admin
        /** @var User $admin */
        $admin = Sanctum::actingAs(
            User::factory()->admin()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create()
        );

        $response = $this->delete(route("delete_team", $user->team->id));

        $response->assertStatus(204);

//        test if data is actually deleted
        $this->assertFalse(Player::whereIn("id",$user->players->pluck("id")->toArray())->exists());
        $this->assertFalse(Team::where("id",$user->team->id)->exists());


        $max_id = Team::query()->max("id") + 1;

        $response = $this->delete(route("delete_team", $max_id));
        $response->assertStatus(404);

    }

    public function test_get_team_players()
    {
        //        seed table with data
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
            ->create();
        $user->load("team.players");

        $response = $this->get(route("team_players",$user->team->id));
        $response->assertStatus(401);

        //         authenticate user
        /** @var User $auth */
        $auth= Sanctum::actingAs(
            User::factory()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create(),
        );

        $auth->load("team.players");
// get team players
        $response = $this->get(route("team_players",$auth->team->id));
        $response->assertOk()->assertExactJson($auth->team->players->toArray());


//       get players of another team
        $response = $this->get(route("team_players",$user->team->id));
        $response->assertStatus(403);


        //         authenticate admin
        /** @var User $admin */
        $admin= Sanctum::actingAs(
            User::factory()->admin()
                ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
                ->create(),
        );

//        get players of another team as admin
        $response = $this->get(route("team_players",$user->team->id));
        $response->assertOk()->assertExactJson($user->team->players->toArray());


        $max_id = Team::query()->max("id") + 1;

//        get players of non existing team
        $response = $this->get(route("team_players", $max_id));
        $response->assertStatus(404);


    }


}
