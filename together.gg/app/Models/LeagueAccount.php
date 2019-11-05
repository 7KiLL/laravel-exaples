<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Storage;

/**
 * App\Models\LeagueAccount
 *
 * @property int $account_id
 * @property int $confirmed
 * @property int $user_id
 * @property string $server
 * @property string|null $id
 * @property string|null $accountId
 * @property string|null $puuid
 * @property string $name
 * @property int|null $profileIconId
 * @property int|null $revisionDate
 * @property int|null $summonerLevel
 * @property string|null $avatar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Entry $entry
 * @property-read User $user
 * @method static bool|null forceDelete()
 * @method static Builder|LeagueAccount newModelQuery()
 * @method static Builder|LeagueAccount newQuery()
 * @method static \Illuminate\Database\Query\Builder|LeagueAccount onlyTrashed()
 * @method static Builder|LeagueAccount query()
 * @method static bool|null restore()
 * @method static Builder|LeagueAccount whereAccountId($value)
 * @method static Builder|LeagueAccount whereAvatar($value)
 * @method static Builder|LeagueAccount whereConfirmed($value)
 * @method static Builder|LeagueAccount whereCreatedAt($value)
 * @method static Builder|LeagueAccount whereDeletedAt($value)
 * @method static Builder|LeagueAccount whereId($value)
 * @method static Builder|LeagueAccount whereName($value)
 * @method static Builder|LeagueAccount whereProfileIconId($value)
 * @method static Builder|LeagueAccount wherePuuid($value)
 * @method static Builder|LeagueAccount whereRevisionDate($value)
 * @method static Builder|LeagueAccount whereServer($value)
 * @method static Builder|LeagueAccount whereSummonerLevel($value)
 * @method static Builder|LeagueAccount whereUpdatedAt($value)
 * @method static Builder|LeagueAccount whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|LeagueAccount withTrashed()
 * @method static \Illuminate\Database\Query\Builder|LeagueAccount withoutTrashed()
 * @mixin Eloquent
 * @property string|null $border
 * @property-read Collection|LeagueTier[] $tiers
 * @method static Builder|LeagueAccount whereBorder($value)
 * @property-read mixed $entry_rank
 * @property-read mixed $entry_tier
 * @property-read Verification $verification
 */
class LeagueAccount extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'account_id';
    protected $guarded = ['account_id'];
    protected $with = ['tiers'];

    /**
     * @return BelongsTo
     * @deprecated
     */
    public function entry()
    {
        return $this->belongsTo(Entry::class, 'entry_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Will be called in the observer
     * @return string
     */
    public function determineBorder()
    {
        $level = $this->summonerLevel;
        $borders = Storage::disk('public')->allFiles('borders');
        $borders = collect($borders)->map(function ($item) {
            preg_match('/\d+/', $item, $res);
            $res = (int)$res[0];
            return $res;
        });
        $borders = $borders->sort()->values()->all();
        $current = $borders[0];
        foreach ($borders as $border) {
            if ($border > $level)
                break;
            $current = $border;
        }
        $url = Storage::disk('public')->url('borders/'.$current.'.png');
        $this->border = $url;
        $this->save();
        return $url;
    }

    /**
     * @return HasMany
     */
    public function tiers()
    {
        return $this->hasMany(LeagueTier::class, 'league_account_id', 'account_id');
    }


    /**
     * @return mixed|string
     */
    public function getEntryTierAttribute()
    {
        $soloq = $this->tiers()->where('queue_type', '=', 'RANKED_SOLO_5x5');
        return $soloq->exists() ? $soloq->first()->tier : 'unranked';
    }

    /**
     * @return int
     */
    public function getEntryRankAttribute()
    {
        $soloq = $this->tiers()->where('queue_type', '=', 'RANKED_SOLO_5x5');
        return $soloq->exists() ? LeagueTier::RANKS[$soloq->first()->rank] : 0;
    }

    /**
     * @return HasOne
     */
    public function verification()
    {
        return $this->hasOne(Verification::class, 'account_id', 'account_id');
    }
}
