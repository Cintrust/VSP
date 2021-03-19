<?php

namespace Tests\Unit;

use App\Models\Market;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerTest extends \Tests\TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_be_instantiated()
    {
        $player = Player::factory()->make();

        $this->assertInstanceOf(Player::class, $player);
        $this->assertLessThanOrEqual(40, $player->age);
        $this->assertGreaterThanOrEqual(18, $player->age);

    }


    public function test_has_a_team()
    {
        $player = Player::factory()->create();
        $player1 = Player::factory()->for(Team::factory())->create();

        $this->assertInstanceOf(Team::class, $player->team);
        $this->assertInstanceOf(Team::class, $player1->team);

    }

    public function test_has_a_user()
    {
        $player = Player::factory()->create();
        $player1 = Player::factory()->for(Team::factory()->for(User::factory()))->create();

        $this->assertInstanceOf(User::class, $player->user);
        $this->assertInstanceOf(User::class, $player1->user);

    }

    public function test_can_be_in_market()
    {
        $player = Player::factory()->has(Market::factory())->create();

        $this->assertInstanceOf(Market::class, $player->market);

        $this->assertEquals($player->id, $player->market->player_id);
        $this->assertEquals($player->team_id, $player->market->seller_id);
    }
}
