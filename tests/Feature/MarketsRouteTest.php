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

class MarketsRouteTest extends TestCase
{
    use WithFaker,RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_markets()
    {
        $markets = Market::factory()->count(23)->create();
        //        seed table with data
        /** @var Player $player */
        $player = Player::factory()->create();

        $response = $this->get(route("get_all_markets"));
        $response->assertStatus(401);

        //         authenticate user
        /** @var User $auth */
        $auth = Sanctum::actingAs(
            $player->user,
        );

        $markets->load(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"]);

//        fetch all from market
        $response = $this->get(route("get_all_markets"));
        $response->assertOk();
        /** @var Market $market */
        $market = $markets->first();
//        fetch all from market with country filtering
        $country = $market->player->country;
        $response = $this->get(route("get_all_markets",
            ["country" => $country]));

        $result = Market::query()->whereHas("player", function ($query) use ($country) {
            $query->where("country", $country);
        })->with(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"])
            ->paginate(20);

        $response->assertOk()->assertExactJson($result->toArray());

        //        fetch all from market with price filtering
        $price = $market->price;
        $response = $this->get(route("get_all_markets",
            ["price" => $price]));

        $result = Market::query()->where("price", $price)
            ->with(["team:id,country,name",
                "player:id,first_name,last_name,country,age,position"])
            ->paginate(20);

        $response->assertOk()->assertExactJson($result->toArray());


        //        fetch all from market with team name filtering
        $team_name = $market->team->name;
        $response = $this->get(route("get_all_markets",
            ["team_name" => $team_name]));

        $result = Market::query()->whereHas("team", function ($query) use ($team_name) {
            $query->where("name", $team_name);
        })->with(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"])
            ->paginate(20);

        $response->assertOk()->assertExactJson($result->toArray());


        //        fetch all from market with player name filtering
        $player_name = $this->faker->randomElement(
            [$market->player->last_name, $market->player->first_name]
        );
        $response = $this->get(route("get_all_markets",
            ["player_name" => $player_name]));

        $result = Market::query()->whereHas("player", function ($query) use ($player_name) {
            $query->where("first_name", $player_name)->orWhere("last_name", $player_name);
        })->with(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"])
            ->paginate(20);

        $response->assertOk()->assertExactJson($result->toArray());


        //        fetch all from market with all filtering
        $player_name = $this->faker->randomElement(
            [$market->player->last_name, $market->player->first_name]
        );
        $response = $this->get(route("get_all_markets",
            ["player_name" => $player_name, "country" => $country,
                "price" => $price, "team_name" => $team_name]));

        $result = Market::query()->where("price", $price)
            ->whereHas("player", function ($query) use ($player_name) {
                $query->where("first_name", $player_name)->orWhere("last_name", $player_name);
            })->whereHas("team", function ($query) use ($team_name) {
                $query->where("name", $team_name);
            })->whereHas("player", function ($query) use ($country) {
                $query->where("country", $country);
            })
            ->with(["team:id,country,name",
                "player:id,first_name,last_name,country,age,position"])
            ->paginate(20);

        $response->assertOk()->assertExactJson($result->toArray());

    }

    public function test_create_market()
    {
        //        seed table with data
        /** @var Player $player */
        $player = Player::factory()->create();

        $data = [
            "price" => $this->faker->randomNumber(),
            "player_id" => $player->id,
        ];

        $response = $this->post(route("create_market"), $data);
        $response->assertStatus(401);

        //         authenticate user
        /** @var User $auth */
        $auth = Sanctum::actingAs(
            $player->user,
        );

//        add player to market
        $response = $this->post(route("create_market"), $data);
        $market = $player->market()->with(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"])->firstOrFail();
        $response->assertCreated()->assertExactJson($market->toArray());


        /** @var Player $player1 */
        $player1 = Player::factory()->create();

        $data = [
            "price" => $this->faker->randomNumber(),
            "player_id" => $player1->id,
        ];

        //        add player from another team to market
        $response = $this->post(route("create_market"), $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["player_id" => "This player is not on your team"]);

        /** @var Player $player2 */
        $player2 = Player::factory()->for(Team::factory()->for(User::factory()->admin()))->create();

        //         authenticate admin
        /** @var User $admin */
        $admin = Sanctum::actingAs(
            $player2->user,
        );

        //        add player from another team to market as admin
        $response = $this->post(route("create_market"), $data);
        $market = $player1->market()->with(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"])->firstOrFail();
        $response->assertCreated()->assertExactJson($market->toArray());

        //        try add player to market again
        $response = $this->post(route("create_market"), $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["player_id" => "This player is already on the market"]);


        $data = [
            "price" => $this->faker->randomNumber(),
            "player_id" => Player::query()->max("id") + 1,
        ];

        //        try add non existing player to market
        $response = $this->post(route("create_market"), $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["player_id"]);

    }

    public function test_get_market()
    {
        /** @var Market $market */
        $market = Market::factory()->create();
        $this->checkUnauthorisedReq("get", route("get_market", $market->id));

        $market->load([
            "newTeam:id,country,name",
            "team:id,country,name",
            "player:id,first_name,last_name,country,age,position"]);

        /** @var User $admin */
        $admin = Sanctum::actingAs(
            User::factory()->admin()->create(),
        );
        $response = $this->get(route("get_market", $market->id));
        $response->assertOk()->assertExactJson($market->toArray());

        $max_id = Market::query()->max("id") + 1;
//        get non existing market
        $response = $this->get(route("get_market", $max_id));
        $response->assertNotFound();
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

    public function test_update_market()
    {
        /** @var Market $market */
        $market = Market::factory()->create();
        /** @var Team $team */
        $team = Team::factory()->create()->refresh();

        $data = [
            'buyer_id' => $team->id,
            "price" => $this->faker->randomNumber()
        ];

        $response = $this->patch(route("update_market", $market->id), $data);

        $response->assertStatus(401);

        //         authenticate user
        /** @var User $auth */
        $auth = Sanctum::actingAs(
            $team->user,
        );

        $old_team = $market->team;

//        buy player
        $response = $this->patch(route("update_market", $market->id), $data);
        $response->assertOk()->assertExactJson(["message" => "Market Details Updated"]);
        $this->assertNotEquals($market->price, $data['price']); // make sure price did not change

//        make sure team budgets are adjusted properly
        $this->assertEquals($old_team->budget + $market->price, $market->team->fresh()->budget);
        $this->assertEquals($team->budget - $market->price, $team->fresh()->budget);


        $this->assertEquals($team->id, $market->player->team_id); //make sure team is swapped

//        make sure new price is within expected range
        $this->assertGreaterThanOrEqual($market->price*1.1, $market->player->market_value);
        $this->assertLessThanOrEqual($market->price*2, $market->player->market_value);

// buy already bought player
        $response = $this->patch(route("update_market", $market->id), $data);
        $response->assertStatus(403);

        /** @var Market $market */
        $market = Market::factory()->create(["price" => $team->budget * 2]);

        // try buy player with less money
        $response = $this->patch(route("update_market", $market->id), $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["buyer_id" => "The team's budget cannot cover the player's market price"]);


        /** @var Team $team */
        $team = Team::factory()->create()->refresh();

        $data = [
            'buyer_id' => $team->id,
            "price"=>1, //change market price
        ];

        // try buy player for another team
        $response = $this->patch(route("update_market", $market->id), $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["buyer_id" => "You need to be the owner of this team to do this"]);


        /** @var User $admin */
        $admin = Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        $old_team = $market->team;

        // try buy player for another team as admin
        $response = $this->patch(route("update_market", $market->id), $data);

        $response->assertOk()->assertExactJson(["message" => "Market Details Updated"]);
        $market->refresh();

//        make sure price changed
        $this->assertEquals($market->price, $data['price']);

//        make sure team budgets are adjusted properly
        $this->assertEquals($old_team->budget + $market->price, $market->team->fresh()->budget);
        $this->assertEquals($team->budget - $market->price, $team->fresh()->budget);


        $this->assertEquals($team->id, $market->player->team_id); //make sure team is swapped

//        make sure new price is within expected range
        $this->assertGreaterThanOrEqual($market->price*1.1, $market->player->market_value);
        $this->assertLessThanOrEqual($market->price*2, $market->player->market_value);


        /** @var Market $market */
        $market = Market::factory()->create();

        // try buy player for non existing team
        $response = $this->patch(route("update_market", $market->id), [
            'buyer_id' => Team::query()->max("id")+1,
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors("buyer_id");


        // try buy player from non existing market
        $response = $this->patch(route("update_market", Market::query()->max("id")+1), [
            'buyer_id' => $team->id,
        ]);
        $response->assertNotFound();

    }


    public function test_delete_market()
    {
        /** @var Market $market */
        $market = Market::factory()->create();
        $this->checkUnauthorisedReq("delete", route("delete_market", $market->id));


        /** @var User $admin */
        $admin = Sanctum::actingAs(
            User::factory()->admin()->create(),
        );

        // delete player as an admin
        $response = $this->delete(route("delete_market", $market->id));
        $response->assertNoContent();
        $this->assertDeleted($market);

//        delete non existing player
        $max_id = Market::query()->max("id") + 1;
        $response = $this->delete(route("delete_market", $max_id));
        $response->assertNotFound();


    }
}
