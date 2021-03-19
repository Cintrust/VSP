<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
 use RefreshDatabase;

    public function test_login()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->post(route("login"),
            ["email"=>$user->email,"password"=>"password"]
        );


//        $response->dump();
        $response->assertStatus(200)->assertJson([
            'token' => true,
        ]);

    }

    public function test_invalid_login()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->post(route("login"),
            ["email"=>$user->email,"password"=>"wrong password"]
        );

        $response->assertStatus(422)->assertJson([
            'message' => true,
            'errors' => true,
        ]);
    }

    public function test_fetch_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->post(route("login"),
            ["email"=>$user->email,"password"=>"password"]
        );

//        $response->dump();
        $response->assertStatus(200)->assertJson([
            'token' => true,
        ]);

        $token = $response->json("token");
        $this->withHeader("Authorization","Bearer $token");


        $testResponse = $this->get(route("me"));
        $this->assertAuthenticated("sanctum");
//        $testResponse->dump();
        $testResponse->assertStatus(200)->assertJsonFragment([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);


    }

    public function test_logout()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->post(route("login"),
            ["email"=>$user->email,"password"=>"password"]
        );


        $response->assertStatus(200)->assertJson([
            'token' => true,
        ]);


        $token = $response->json("token");
        $this->withHeader("Authorization","Bearer $token");


        $value = $user->tokens()->value("id");
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $value,
        ]);


        $testResponse = $this->post(route("logout"));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $value,
        ]);

        $testResponse->assertNoContent();



    }

}
