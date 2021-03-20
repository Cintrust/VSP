<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminApiRequest;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use GeneratePlayers;
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(AdminApiRequest $request)
    {
        $query = User::query();

        if ($request->input("with_resource")) {
            $query->with("team.players");
        }

        $users = $query->paginate(10);
        return response($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            'email' => ['bail', 'required', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['bail', 'required', 'string', 'min:8', "max:30"],
            'team_name' => ['bail',"sometimes", 'required', 'string', 'min:3', 'max:60','unique:teams,name'],
            'team_country' => ['bail',"sometimes", 'required', 'string', 'min:3', 'max:60'],

        ]);

        $user = User::create([
            "name" => $data['name'],
            "email" => $data['email'],
            "password" => Hash::make($data['password']),
        ]);


        $faker = \Faker\Factory::create();

        $team = Team::create([
            "user_id" => $user->id,
            "country" => $request->input("team_country",$faker->country),
            "name" => $request->input("team_name",$faker->name." FC"),
        ]);

        $this->generatePlayers($team->id);

        $team->load("players");


        $user->setRelation("team",$team);



        return response($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param AdminApiRequest $request
     * @param User $user
     * @return Response
     */
    public function show(AdminApiRequest $request, User $user)
    {
        if ($request->input("with_resource")) {
            $user->load("team.players");
        }

        return response($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AdminApiRequest $request
     * @param User $user
     * @return Response
     */
    public function update(AdminApiRequest $request, User $user)
    {
        $data = $request->validate([
            'name' => ['bail', "required", 'sometimes', 'string', 'min:3', 'max:60'],
            'email' => ['bail', "required", 'sometimes', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['bail', "required", 'sometimes', 'string', 'min:8', "max:30"]
        ]);

        if (isset($data["password"])) {
            $data["password"] = Hash::make($data['password']);
        }

        $user->update($data);


        return response(["message" => "User Details Updated"]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdminApiRequest $request
     * @param User $user
     * @return Response
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function destroy(AdminApiRequest $request, User $user): Response
    {
//        check if admin wanted to delete him self
        if (Auth::id() == $user->id) {
            throw  new AuthorizationException("You are not allowed to delete yourself");
        }


        $user->delete();

        return response(null, 204);
    }
}
