<?php

namespace App\Models\Actors;

use App\Http\Resources\Actors\UserResource;
use App\Indices\InlineIndexConfigurator;
use App\Indices\UserIndexConfigurator;
use App\Interfaces\IScorable;
use App\Models\About\Education;
use App\Models\About\Work;
use App\Models\Common\Score;
use App\Models\Common\ScoreStat;
use App\Models\Common\SelfAssessment;
use App\Models\Common\Timeline;
use App\Traits\BindUUID;
use App\Traits\FixedFriendable;
use App\Traits\GeoScopes;
use Eloquent;
use Hootlex\Friendships\Models\FriendFriendshipGroups;
use Hootlex\Friendships\Models\Friendship;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Khsing\World\Models\City;
use Khsing\World\Models\Continent;
use Khsing\World\Models\Country;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use ScoutElastic\Searchable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Storage;

/**
 * App\Models\Actors\User
 *
 * @property mixed $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property int|null $phone
 * @property string $gender
 * @property string $birth
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Client[] $clients
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read Collection|Token[] $tokens
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereBirth($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|Company[] $companies
 * @property-read Collection|Education[] $educations
 * @property-read Collection|Timeline[] $timeline
 * @property string|null $email_verified_at
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @property-read Collection|Timeline[] $timelined
 * @property-read Collection|SocialAccount[] $socials
 * @property string|null $deleted_at
 * @property-read Collection|Permission[] $permissions
 * @property-read Collection|Role[] $roles
 * @method static Builder|User permission($permissions)
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereDeletedAt($value)
 * @property-read Collection|Friendship[] $friends
 * @property-read Collection|FriendFriendshipGroups[] $groups
 * @property-read Company $company
 * @property string|null $religion
 * @property string|null $marital
 * @property string|null $nation_code
 * @method static Builder|User whereCurrentCity($value)
 * @method static Builder|User whereCurrentContinent($value)
 * @method static Builder|User whereCurrentCountry($value)
 * @method static Builder|User whereHomeCity($value)
 * @method static Builder|User whereHomeContinent($value)
 * @method static Builder|User whereHomeCountry($value)
 * @method static Builder|User whereMarital($value)
 * @method static Builder|User whereNationCode($value)
 * @method static Builder|User whereReligion($value)
 * @property-read string|null $avatar
 * @property-read string|null $header
 * @property-read Collection|Work[] $works
 * @property int|null $home_city
 * @property int|null $current_city
 * @property int|null $home_country
 * @property int|null $current_country
 * @property int|null $home_continent
 * @property int|null $current_continent
 * @property-read City|null $current_city_geo
 * @property-read Continent|null $current_continent_geo
 * @property-read Country|null $current_country_geo
 * @property-read City|null $home_city_geo
 * @property-read Continent|null $home_continent_geo
 * @property-read Country|null $home_country_geo
 * @method static Builder|User inCity($value)
 * @method static Builder|User inContinent($value)
 * @method static Builder|User inCountry($value)
 * @property-read Collection|Score[] $score
 * @property-read Collection|Score[] $scores
 * @property-read ScoreStat $stats
 * @property-read \App\Models\Common\SelfAssessment $self_assessment
 * @property-read mixed $is_completed_self_assessment
 * @property \ScoutElastic\Highlight|null $highlight
 */
class User extends Authenticatable implements MustVerifyEmail, IScorable
{
    use HasApiTokens, Notifiable, HasRoles, FixedFriendable, BindUUID, GeoScopes, Searchable;


    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $guard_name = 'api';
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'email'
    ];

    protected $casts = [
        'id' => 'string', // Cast it to UUID is kinda bad idea
        'phone' => 'int'
    ];

    protected $mapping = [
        'properties' => [
            'first_name' => [
                "analyzer" => "autocomplete",
                "search_analyzer" => "standard",
                'type' => 'text'
            ],
            'last_name' => [
                "analyzer" => "autocomplete",
                "search_analyzer" => "standard",
                'type' => 'text'
            ],
        ]
    ];

    public function toSearchableArray()
    {
        return $this->toArray();
    }

    protected $indexConfigurator = UserIndexConfigurator::class;



    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'email', 'email');
    }

    /**
     * @return HasMany
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class, 'user_id', 'id');
    }

    /**
     * User's timeline
     * @return MorphMany
     */
    public function timeline(): MorphMany
    {
        return $this->morphMany(Timeline::class, 'timelinable');
    }

    /**
     * User's posted timelines to other entities
     * @return HasMany
     */
    public function timelined(): HasMany
    {
        return $this->hasMany(Timeline::class, 'author_id', 'id')->without('author');
    }

    /**
     * @return HasMany
     */
    public function socials(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'user_id', 'id');
    }


    /**
     * Sanitize phone number
     * @param $value
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = preg_replace('/\D/i', '', $value);
    }

    public function items()
    {
        //TODO implement this
    }

    /**
     * @return HasOne
     */
    public function self_assessment(): HasOne
    {
        return $this->hasOne(SelfAssessment::class, 'user_id', 'id')
            ->withDefault(collect(Score::SCORE_LIST)->flip()->transform(function ($iem) {
                return $item = 0;
            })->put('stub', null)->toArray());
    }

    /**
     * Give the link to avatar
     * @return string|null
     */
    public function getAvatarAttribute()
    {
        if (Storage::exists('avatars/' . $this->id)) {
            return Storage::url('avatars/' . $this->id);
        }
        return Storage::url('default/default_avatar.jpg');
    }

    /**
     * Get header URL
     * @return string|null
     */
    public function getHeaderAttribute()
    {
        if (Storage::exists('headers/' . $this->id)) {
            return Storage::url('headers/' . $this->id);
        }
        return null;
    }

    /**
     * @return MorphMany
     */
    public function scores(): MorphMany
    {
        return $this->morphMany(Score::class, 'scorable');
    }

    /**
     * Get the total percent of Score average
     * @return int|null
     */
    public function getScoreAttribute()
    {
        return \Cache::remember('stats:' . $this->id, config('reputable.stats.users.frequency'), function () {
            return $this->stats->total ?? null;
        });
    }

    /**
     * @return MorphOne
     */
    public function stats(): MorphOne
    {
        return $this->morphOne(ScoreStat::class, 'scorable');
    }

    /**
     * @return array|\Illuminate\Support\Collection
     * @throws AuthenticationException
     */
    public function generateQuestions()
    {
        if (!auth()->check())
            throw new AuthenticationException();

        /** @var User $user */
        $user = \Auth::user();
        $scoreQuery = $this->scores()->where('user_id', '=', $user->id);
        /**
         * User hasn't score current model
         */
        if (!$scoreQuery->exists()) {
            return Score::SCORE_LIST;
        }
        $score = collect($scoreQuery->first());

        return $score->filter(function ($value) {
            return $value === null;
        })->keys();
    }

    public function getIsCompletedSelfAssessmentAttribute()
    {
        if (!$assessment = $this->self_assessment)
            return false;
        $collection = collect($assessment->toArray());
        return $collection->every(function ($item) {
            return !is_null($item);
        });
    }

    /**
     * Geo stuff
     * @deprecated
     */

    public function home_country_geo(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'home_country', 'id');
    }

    public function current_country_geo(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'current_country', 'id');
    }

    public function home_city_geo(): BelongsTo
    {
        return $this->belongsTo(City::class, 'home_city', 'id');
    }

    public function current_city_geo(): BelongsTo
    {
        return $this->belongsTo(City::class, 'current_city', 'id');
    }

    public function home_continent_geo(): BelongsTo
    {
        return $this->belongsTo(Continent::class, 'home_continent', 'id');
    }

    public function current_continent_geo(): BelongsTo
    {
        return $this->belongsTo(Continent::class, 'current_continent', 'id');
    }

}
