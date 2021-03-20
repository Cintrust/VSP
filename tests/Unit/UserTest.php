<?php

namespace Tests\Unit;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends \Tests\TestCase
{
//    use  WithFaker,RefreshDatabase;
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_be_instantiated()
    {
        $user = User::factory()->make();
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_can_have_different_role()
    {
        $user = User::factory()->make();
        $admin = User::factory()->admin()->make();

        $this->assertNotEquals(User::ADMIN, $user->isAdmin());
        $this->assertEquals(User::ADMIN, $admin->isAdmin());

    }

    public function test_has_a_team()
    {
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->count(1))
//            ->has()
            ->create();

        $this->assertInstanceOf(Team::class, $user->team);
        $this->assertEquals($user->id, $user->team->user_id);
        $this->assertEquals(1, $user->team()->count());

    }

    public function test_has_players()
    {
        /** @var User $user */
        $user = User::factory()
            ->has(Team::factory()->has(Player::factory()->count(20))->count(1))
//            ->has()
            ->create();

        $this->assertInstanceOf(Player::class, $user->players->first());
        $this->assertTrue($user->is($user->players->first()->user));
        $this->assertEquals(20, $user->players->count());
    }
}
