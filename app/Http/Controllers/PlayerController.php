<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminApiRequest;
use App\Http\Requests\PlayerApiRequest;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AdminApiRequest $request)
    {
        $query = Player::query();

        if ($request->input("with_resource")) {
            $query->with(["team:teams.id,name,country", "user:users.id,users.name,email"]);
        }

        $players = $query->paginate(10);
        return response($players);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AdminApiRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminApiRequest $request)
    {
        $data = $request->validate([
            "team_id" => ['bail',"sometimes", 'required', 'integer', 'min:1', "exists:teams,id"],
            'country' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            'first_name' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            'last_name' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            "age" => ['bail', 'required', 'integer', 'min:18', "max:40"],
            "position" => ['bail',"sometimes", 'required', 'min:1', "max:40","in:attacker,midfielder,defender,goalkeeper"],
            "market_value" => ['bail', "sometimes", 'required', 'integer', 'min:0']
        ]);

        $player = Player::create($data);

        return response($player, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param AdminApiRequest $request
     * @param \App\Models\Player $player
     * @return \Illuminate\Http\Response
     */
    public function show(AdminApiRequest $request, Player $player)
    {
        if ($request->input("with_resource")) {
            $player->load(["team:teams.id,name,country", "user:users.id,users.name,email"]);
        }

        return response($player);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param PlayerApiRequest $request
     * @param \App\Models\Player $player
     * @return \Illuminate\Http\Response
     */
    public function update(PlayerApiRequest $request, Player $player)
    {
        $rules = [
            'country' => ['bail',"sometimes", 'required', 'string', 'min:3', 'max:60'],
            'first_name' => ['bail', "sometimes", 'required', 'string', 'min:3', 'max:60'],
            'last_name' => ['bail', "sometimes",'required', 'string', 'min:3', 'max:60'],
        ];

        if (Auth::user()->isAdmin()) {
//            add validation to parameters only admin is allowed to change
            $rules += [
                "age" => ['bail',"sometimes", 'required', 'integer', 'min:18', "max:40"],
                "market_value" => ['bail', "sometimes", 'required', 'integer', 'min:0']
            ];
        }

        $data = $request->validate($rules);


        $player->update($data);


        return response(["message" => "Player Details Updated"]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdminApiRequest $request
     * @param \App\Models\Player $player
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AdminApiRequest $request, Player $player)
    {
        $player->delete();

        return response(null, 204);
    }
}
