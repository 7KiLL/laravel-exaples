<?php

namespace App\Http\Controllers\Riot;

use App\Http\Requests\Account\CreateAccount;
use App\Models\Entry;
use App\Models\LeagueAccount;
use App\Models\Tier;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = LeagueAccount::all();
        return view('account.index', ['accounts' => $accounts]);
    }

    public function create()
    {
        if (!Auth::user()->account()->exists())
            return view('account.create');
        Session::flash('status', 'You already have an account!');
        Session::flash('type', 'danger');
        return redirect()->route('home');
    }

    /**
     * @param CreateAccount $account
     * @return \Illuminate\Http\RedirectResponse
     * @throws \RiotAPI\LeagueAPI\Exceptions\GeneralException
     * @throws \RiotAPI\LeagueAPI\Exceptions\SettingsException
     * @throws \RiotAPI\DataDragonAPI\Exceptions\SettingsException
     * @throws \RiotAPI\DataDragonAPI\Exceptions\RequestException
     */
    public function store(CreateAccount $account)
    {
        if (!$account->validated())
            return redirect()->back()->with($account->validated())->withInput();

        $rito = new LeagueAPIController(strtolower($account->get('server')));
        $summoner = $rito->getSummoner($account->get('name'));
        $summoner['server'] = $account->get('server');

        LeagueAccount::create([
                'user_id' => Auth::id(),
            ] + $summoner);
        Session::flash('status', 'You\'ve successfully created an account!');
        return redirect()->route('account.verify');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $account = Auth::user()->account();
        if (!$account->exists()) {
            return view('account.create');
        }
        return view('account.update', ['account' => $account->firstOrFail()]);
    }

    public function update()
    {

    }

    /**
     * @param LeagueAccount $account
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws Exception
     */
    public function delete(LeagueAccount $account)
    {
        $account->delete();
        return redirect()->route('account.create');
    }

    public function verification()
    {
        return view('account.verification', ['user' => Auth::user()]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \RiotAPI\LeagueAPI\Exceptions\GeneralException
     * @throws \RiotAPI\LeagueAPI\Exceptions\SettingsException
     */
    public function verifyAccount()
    {
        $account = Auth::user()->account;
        $verify = new VerificationController($account);
        try {$check = $verify->checkCode($verify->retrieveVerification());}
        catch (Exception $exception) {
            $check = false;
        }
        if ($check) {
                $verify->getVerification()->update(['verified' => true]);
                $account->update(['confirmed' => true]);
                if (Entry::onlyTrashed()->where('user_id', '=', Auth::id())->exists()) {
                $softDeleted = Entry::onlyTrashed()->where('user_id', Auth::id())->pluck('id')->toArray();
                (new Tier)->whereIn('entry_id', $softDeleted)->update([
                    'tier' => Auth::user()->account->entry_tier,
                    'division' => Auth::user()->account->entry_rank,
                ]);
                Entry::onlyTrashed()->where('user_id', Auth::id())->restore();
            }
            return redirect()->route('entry.create');
        } else {
            Session::flash('status', 'Verification failed.');
            Session::flash('type', 'danger');
            return view('account.verification', ['user' => Auth::user()]);
        }
    }



    /**
     */
    public static function updateAccounts()
    {
        (new LeagueAccount)->where('updated_at', '<=', Carbon::now()->subHours(12))->chunk(25, function (Collection $accounts) {
            $accounts->map(function (LeagueAccount $account) {
                /**
                 * Create an instance for API calls
                 */
                $api = new LeagueAPIController($account->server);

                /**
                 * Get new level and border
                 */
                $border = $api->getSummonerById($account->id);
                $account->update($border);

                $account->determineBorder();

                /**
                 * Get new positions for summoner
                 */
                $ladder = $api->getShittyLadder($account->id);
                if (!$ladder) {
                    /**
                     * For some reason library don't want to get ladder by encrypted ID. Okay, let it be.
                     */
                    $ladder = $api->getLadder($account->name);
                }

                foreach ($ladder as $key => $dto) {
                    $account->tiers()->updateOrCreate(
                        [
                            'queue_type' => $dto->queueType,
                        ],
                        [
                            'tier' => $dto->tier,
                            'rank' => $dto->rank,
                            'league_name' => '',
                            'wins' => $dto->wins,
                            'losses' => $dto->losses,
                            'lp' => $dto->leaguePoints,
                            'league_id' => $dto->leagueId
                        ]
                    );
                }
                $account->touch();
            });
        });

    }
}
