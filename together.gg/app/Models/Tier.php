<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\Tier
 *
 * @property int $entry_id
 * @property int $division
 * @property string $this
 * @method static Builder|Tier newModelQuery()
 * @method static Builder|Tier newQuery()
 * @method static Builder|Tier query()
 * @method static Builder|Tier whereDivision($value)
 * @method static Builder|Tier whereEntryId($value)
 * @method static Builder|Tier whereTier($value)
 * @mixin Eloquent
 * @property string $tier
 * @property-read LeagueAccount $account
 * @property-read string $emblem
 * @property-read string $rank
 * @property-read mixed $data_order
 */
class Tier extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'entry_id';
    protected $fillable = [
        'tier',
        'division',
    ];

    protected $appends = ['emblem', 'data_order'];
    /**
     * @var array highRanks
     * List of high ranks without divisions
     */
    const highRanks = [
        'master',
        'grandmaster',
        'challenger'
    ];

    /**
     * Actually we've checked it in observer, but to make sure
     * @return int
     */
    public function getDivisionAttribute()
    {
        if (in_array($this->tier, self::highRanks) && $this->attributes['division'] !== 1) {
            $this->attributes['division'] = 1;
            $this->save();
            return $this->attributes['division'];
        }
        return $this->attributes['division'] > 0 ? $this->attributes['division'] : '';
    }

    /**
     * Generating rank emblem url
     * @return string
     */
    public function getEmblemAttribute()
    {
        return Storage::disk('public')->url('ladder/'.$this->tier.'.png');
    }

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(LeagueAccount::class, 'account_id', 'id');
    }

    /**
     * @return string
     */
    public function getRankAttribute()
    {
        return ucfirst($this->tier) . ' ' . $this->division;
    }

    public function getDataOrderAttribute()
    {
        $tiers = array_flip(Entry::TIERS);
        return (int)(10 * (int)$tiers[$this->tier] - (int)$this->attributes['division']);
    }
}

