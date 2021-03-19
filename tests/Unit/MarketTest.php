<?php

namespace Tests\Unit;

use App\Models\Market;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class MarketTest extends \Tests\TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_be_instantiated()
    {
        $market = Market::factory()->make();

        $this->assertInstanceOf(Market::class,$market);
    }

    public function test_has_a_player()
    {
        $market = Market::factory()->create();
        $market1 = Market::factory()->for(Player::factory())->create();

        $this->assertInstanceOf(Player::class,$market->player);
        $this->assertInstanceOf(Player::class,$market1->player);

        $this->assertEquals($market->seller_id,$market->player->team_id);

        $this->assertEquals($market1->seller_id,$market1->player->team_id);

    }
}
