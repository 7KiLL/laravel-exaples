<?php

namespace App\Observers;

use App\Http\Controllers\Riot\LeagueAPIController;
use App\Http\Controllers\Riot\VerificationController;
use App\Models\LeagueAccount;
use App\Models\LeagueTier;
use Illuminate\Support\Facades\Auth;
use RiotAPI\LeagueAPI\Objects\LeaguePositionDto;

class LeagueAccountObserver
{

    public function creating(LeagueAccount $leagueAccount)
    {
        if (Auth::check() && Auth::user()->account()->exists())
            return abort(403, 'You already has an account!');
    }

    /**
     * Handle the league account "created" event.
     *
     * @param  \App\Models\LeagueAccount $leagueAccount
     * @return void
     * @throws \RiotAPI\LeagueAPI\Exceptions\GeneralException
     * @throws \RiotAPI\LeagueAPI\Exceptions\SettingsException
     * @throws \Exception
     */
    public function created(LeagueAccount $leagueAccount)
    {
        $leagueAccount->determineBorder();

        $api = new LeagueAPIController($leagueAccount->server);
        $ladder = $api->getLadder($leagueAccount->name);
        $data = [];
        foreach ($ladder as $key => $dto) {
            $data[] = LeagueTier::make([
                'queue_type' => $dto->queueType,
                'tier' => $dto->tier,
                'rank' => $dto->rank,
                'league_name' => '',
                'wins' => $dto->wins,
                'losses' => $dto->losses,
                'lp' => $dto->leaguePoints,
                'league_id' => $dto->leagueId
            ]);
        }
        $leagueAccount->tiers()->saveMany($data);
    }

    /**
     * Handle the league account "updated" event.
     *
     * @param  \App\Models\LeagueAccount $leagueAccount
     * @return void
     */
    public function updated(LeagueAccount $leagueAccount)
    {
        //
    }

    /**
     * Handle the league account "deleted" event.
     *
     * @param  \App\Models\LeagueAccount $leagueAccount
     * @return void
     */
    public function deleted(LeagueAccount $leagueAccount)
    {
        $leagueAccount->user->entries()->delete();
    }

    /**
     * Handle the league account "restored" event.
     *
     * @param  \App\Models\LeagueAccount $leagueAccount
     * @return void
     */
    public function restored(LeagueAccount $leagueAccount)
    {
        //
    }

    /**
     * Handle the league account "force deleted" event.
     *
     * @param  \App\Models\LeagueAccount $leagueAccount
     * @return void
     */
    public function forceDeleted(LeagueAccount $leagueAccount)
    {
        //
    }
}
