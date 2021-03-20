<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminApiRequest;
use App\Http\Requests\TeamApiRequest;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    use GeneratePlayers;

    /**
     * Display a listing of the resource.
     *
     * @param TeamApiRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(AdminApiRequest $request)
    {
        $query = Team::query();

        if ($request->input("with_resource")) {
            $query->with(["players", "user"]);
        }

        $teams = $query->paginate(10);
        return response($teams);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id_rule = ['bail', 'required', 'integer', 'min:1', 'unique:teams,user_id'];

        if (Auth::user()->isAdmin()) {
//            make sure the user exists on users table
            $user_id_rule[] = "exists:users,id";
        } else {
            $user_id_rule[] = function ($attribute, $value, $fail) {
                if ($value != Auth::id()) {
                    $fail('You are not allowed to create team for another user');
                }
            };
        }
        $data = $request->validate([
            'name' => ['bail', 'required', 'string', 'min:3', 'max:60','unique:teams,name'],
            'country' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            'user_id' => $user_id_rule,
        ], ["user_id.unique" => "user already has a team"]);


        $team = Team::create($data);

        $this->generatePlayers($team->id);


        $team->refresh()->load("players");


        return response($team, 201);


    }

    /**
     * Display the specified resource.
     *
     * @param TeamApiRequest $request
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\Response
     */
    public function show(TeamApiRequest $request, Team $team)
    {
        if ($request->input("with_resource")) {
            $team->load("players");
        }
        return response($team);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\Response
     */
    public function update(TeamApiRequest $request, Team $team)
    {

        $data = $request->validate([
            'name' => ['bail', "sometimes", 'required', 'string', 'min:3', 'max:60',"unique:teams"],
            'country' => ['bail', "sometimes", 'required', 'string', 'min:3', 'max:60'],

        ]);

        $team->update($data);

        return response(["message" => "Team Details Updated"]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdminApiRequest $request
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AdminApiRequest $request, Team $team)
    {
        $team->delete();

        return response(null, 204);
    }

    public function players(TeamApiRequest $request, Team $team)
    {
        $collection = $team->players()->get();

        return response($collection);
    }
}
