<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminApiRequest;
use App\Http\Requests\StoreMarketApiRequest;
use App\Http\Requests\UpdateMarketApiRequest;
use App\Models\Market;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $request->validate([
            "country" => "bail|sometimes|required|string|min:3",
            "team_name" => "bail|sometimes|required|string|min:3",
            "player_name" => "bail|sometimes|required|string|min:3",
            "price" => "bail|sometimes|required|integer|min:0",
        ]);
        $builder = Market::query();

        $builder->whereNull("buyer_id")
            ->with(["team:id,country,name",
                "player:id,first_name,last_name,country,age,position"]);

        $country = $request->query("country");
        $price = $request->query("price");
        $team_name = $request->query("team_name");
        $player_name = $request->query("player_name");

        if ($country) {
//            filter results with players country
            $builder->whereHas("player", function ($query) use ($country) {
                $query->where("country", $country);
            });
        }

        if ($price) {
//            filter result with price
            $builder->where("price", $price);
        }

        if ($player_name) {
//            filter result by player name: first or last
            $builder->whereHas("player", function ($query) use ($player_name) {
                $query->where("first_name", $player_name)->orWhere("last_name", $player_name);
            });
        }

        if ($team_name) {
//            filter result with team name
            $builder->whereHas("team", function ($query) use ($team_name) {
                $query->where("name", $team_name);
            });
        }


        $markets = $builder->paginate(20);
        return response($markets);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreMarketApiRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMarketApiRequest $request)
    {


        $data = $request->all();
        $player = Player::findOrFail($data["player_id"]);


        $market = Market::create([
            "seller_id" => $player->team_id,
            "player_id" => $player->id,
            "price" => $data['price'],
        ])->refresh();

        $market->load(["team:id,country,name",
            "player:id,first_name,last_name,country,age,position"]);



        return response($market, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param AdminApiRequest $request
     * @param \App\Models\Market $market
     * @return \Illuminate\Http\Response
     */
    public function show(AdminApiRequest $request, Market $market)
    {
        $market->load([
            "newTeam:id,country,name",
            "team:id,country,name",
            "player:id,first_name,last_name,country,age,position"]);


        return response($market);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Market $market
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws \Exception
     */
    public function update(UpdateMarketApiRequest $request, Market $market)
    {
        $rules = ["buyer_id" => "bail|sometimes|required|min:1|exists:teams,id"];
        if (Auth::user()->isAdmin()) {
//            add validation to inputs only is allowed to change
            $rules += ["price" => "bail|sometimes|required|integer|min:0"];
        }

        $data = $request->validate($rules);


        if (isset($data["buyer_id"])) {

            $team = Team::findOrFail($data["buyer_id"]);

//            make sure user making the request own the team or is admin
            if (!Auth::user()->isAdmin() && $team->user_id != Auth::id()) {
                throw ValidationException::withMessages([
                    'buyer_id' => ['You need to be the owner of this team to do this'],
                ]);
            }

//            make sure the team has enough money to cover the player market price
            if (($data["price"] ?? $market->price) > $team->budget) {
                throw ValidationException::withMessages([
                    'buyer_id' => ["The team's budget cannot cover the player's market price"],
                ]);
            }

            $market->update($data);
//            get ratio multiplier ie between 1.1 and 2
            $ratio_increase = random_int(110, 200) / 100;

//            increase price
            $amount = $market->price * $ratio_increase;

            /** @var Player $player */
            $player = $market->player()->firstOrFail();

//            increase the budget of the old team
            $player->team()->increment("budget", $market->price);

//            transfer player to new team and update new market price
            $player->update([
                "team_id" => $data["buyer_id"],
                "market_value" => $amount
            ]);

//            decrease the budget of the new team
            $player->team()->decrement("budget", $market->price);


        } else {
//            no buyer, so update market price if available
            $market->update($data);
        }


        return response(["message" => "Market Details Updated"]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdminApiRequest $request
     * @param \App\Models\Market $market
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AdminApiRequest $request, Market $market)
    {
        $market->delete();

        return response(null, 204);
    }
}
