<?php

namespace Tests\Unit;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class TeamTest extends \Tests\TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_be_instantiated()
    {
        $team = Team::factory()->make();
        $this->assertInstanceOf(Team::class, $team);
    }

    public function test_has_a_user()
    {
        $team = Team::factory()->create();
        $team1 = Team::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $team->user);
        $this->assertInstanceOf(User::class, $team1->user);


        $this->assertEquals($team->id, $team->user->team->id);
        $this->assertEquals($team1->id, $team1->user->team->id);


    }

    public function test_has_players()
    {
        $team = Team::factory()
            ->has(Player::factory()->count(20))->create();

        $this->assertInstanceOf(Player::class, $team->players->first());

        $this->assertTrue($team->is($team->players->first()->team));

        $this->assertEquals($team->team_value, $team->players->sum("market_value"));
        $this->assertEquals(20, $team->players->count());



    }
}
