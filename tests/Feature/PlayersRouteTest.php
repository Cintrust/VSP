<?php

namespace Tests\Feature;

use App\Models\Market;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlayersRouteTest extends TestCase
{
    use WithFaker,RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_players()
    {
        $this->checkUnauthorisedReq("get", route("get_all_players"));

        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $response = $this->get(route("get_all_players"));

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

    public function test_create_player()
    {
        $data = [
            "country" => $this->faker->country,
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "age" => $this->faker->numberBetween(18, 40),
            "market_value" => $this->faker->numberBetween()
        ];
        $this->checkUnauthorisedReq("post", route("create_player"), $data);

        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

//        create player without a team
        $response = $this->post(route("create_player"), $data);
        $response->assertCreated()->assertJsonFragment($data);

        /** @var Team $team */
        $team = Team::factory()->create();

        $data = [
            "team_id" => $team->id,
            "country" => $this->faker->country,
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "age" => $this->faker->numberBetween(18, 40),
            "market_value" => $this->faker->numberBetween()
        ];

        //        create player with a team
        $response = $this->post(route("create_player"), $data);
        $response->assertCreated()->assertJsonFragment($data);

        $data = [
            "team_id" => Team::query()->max('id') + 1,
            "country" => $this->faker->country,
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "age" => $this->faker->numberBetween(18, 40),
            "market_value" => $this->faker->numberBetween()
        ];
        //        create player with a non existing team
        $response = $this->post(route("create_player"), $data);
        $response->assertStatus(422)->assertJsonValidationErrors("team_id");

    }

    public function test_get_player()
    {
        /** @var Player $player */
        $player = Player::factory()->create();
        $this->checkUnauthorisedReq("get", route("get_player", $player->id));


        //        authenticate User as admin
        Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

//        get player
        $response = $this->get(route("get_player", $player->id));

        $response->assertOk()->assertExactJson($player->toArray());

        //        get player with resource
        $response = $this->get(route("get_player", [
            "player" => $player->id, "with_resource" => true]));
        $player->load(["team:teams.id,name,country", "user:users.id,users.name,email"]);

        $response->assertOk()->assertExactJson($player->toArray());

//        get non existing player
        $response = $this->get(route("get_player", Player::query()->max("id") + 1));

        $response->assertNotFound();
    }

    public function test_update_player()
    {
        /** @var Player $player */
        $player = Player::factory()->create();

        $data = [
            "country" => $this->faker->randomNumber() . "_" . $this->faker->country,
            "first_name" => $this->faker->randomNumber() . "_" . $this->faker->firstName,
            "last_name" => $this->faker->randomNumber() . "_" . $this->faker->lastName,
            "age" => $player->age == 40 ? 39 : $player->age + 1, //make sure a different age is used
            "market_value" => $this->faker->numberBetween(10, 400000)
        ];

        $this->patch(route("update_player", $player->id), $data)->assertStatus(401);


        //         authenticate user
        /** @var User $auth */
        $auth = Sanctum::actingAs(
            $player->user,
        );

//        update player details
        $response = $this->patch(route("update_player", $player->id), $data);
        $response->assertOk()->assertExactJson(["message" => "Player Details Updated"]);
        $player->refresh();
//make sure these values were updated
        $this->assertEquals($player->country, $data['country']);
        $this->assertEquals($player->first_name, $data['first_name']);
        $this->assertEquals($player->last_name, $data['last_name']);
//        make sure these values were not updated
        $this->assertNotEquals($player->age, $data['age']); //only admin can change this values
        $this->assertNotEquals($player->market_value, $data['market_value']);  //only admin can change this values


        /** @var Player $player */
        $player = Player::factory()->create();

        $data = [
            "country" => $this->faker->country,
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "age" => $player->age == 40 ? 39 : $player->age + 1, //make sure a different age is used
            "market_value" => $this->faker->numberBetween()
        ];

//        update details of player from another team
        $response = $this->patch(route("update_player", $player->id), $data);
        $response->assertStatus(403);

        /** @var Player $player1 */
        $player1 = Player::factory()->for(Team::factory()->for(User::factory()->admin()))->create();


        //         authenticate admin
        /** @var User $admin */
        $admin = Sanctum::actingAs(
            $player1->user,
        );

        //        update details of player from another team as admin
        $response = $this->patch(route("update_player", $player->id), $data);

        $response->assertOk()->assertExactJson(["message" => "Player Details Updated"]);
        $player->refresh();
//make sure these values were updated
        $this->assertEquals($player->country, $data['country']);
        $this->assertEquals($player->first_name, $data['first_name']);
        $this->assertEquals($player->last_name, $data['last_name']);
        $this->assertEquals($player->age, $data['age']); //only admin can change this values
        $this->assertEquals($player->market_value, $data['market_value']);  //only admin can change this values


        //        update details of non existing player
        $response = $this->patch(route("update_player", Player::query()->max("id") + 1), $data);

        $response->assertNotFound();


    }

    public function test_delete_player()
    {
        /** @var Player $player */
        $player = Player::factory()->has(Market::factory())->create();;
        $this->checkUnauthorisedReq("delete", route("delete_player", $player->id));

        /** @var Player $player1 */
        $player1 = Player::factory()->for(Team::factory()->for(User::factory()->admin()))->create();


        //         authenticate admin
        /** @var User $admin */
        $admin = Sanctum::actingAs(
            $player1->user,
        );
        $player->load("market");
// delete player as an admin
        $response = $this->delete(route("delete_player", $player->id));
        $response->assertNoContent();
        $this->assertDeleted($player->market);
        $this->assertDeleted($player);

//        delete non existing player
        $max_id = Player::query()->max("id") + 1;
        $response = $this->delete(route("delete_player", $max_id));
        $response->assertNotFound();


    }
}
