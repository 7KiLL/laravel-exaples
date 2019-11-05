<?php

namespace App;

use App\Models\Chat;
use App\Models\Comment;
use App\Models\Entry;
use App\Models\Grade;
use App\Models\LeagueAccount;
use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Chat[] $chats
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int $dislikes
 * @property-read int $likes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Grade[] $grades
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $isAdmin
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIgn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAdmin($value)
 * @property-read \App\Models\LeagueAccount $account
 * @property string|null $about
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAbout($value)
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email', 'password',
//    ];

    protected $guarded = ['id'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'isAdmin', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['account'];

    protected $appends = ['likes', 'dislikes'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries() : HasMany
    {
        return $this->hasMany(Entry::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grades() : HasMany
    {
        return $this->hasMany(Grade::class, 'graded_id', 'id');
    }

    /**
     * Simply counts likes of user
     * @return int
     */
    public function getLikesAttribute() : int
    {
        return $this->grades()->where('grade', '=', true)->get()->count();
    }

    /**
     * Simply counts dislikes of user
     * @return int
     */
    public function getDislikesAttribute() : int
    {
        return $this->grades()->where('grade', '=', false)->get()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chats() : HasMany
    {
        return $this
            ->hasMany(Chat::class, 'first_user', 'id')
            ->orWhere('second_user', $this->id)
            ->distinct();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments() : HasMany
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function account() : HasOne
    {
        return $this->hasOne(LeagueAccount::class, 'user_id', 'id');
    }


    /**
     * UPD we are removing name from User model because we already have it in LeagueAccount
     * This attribute is for capability
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->account->name;
    }

    /**
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model|null|object|
     */
    public function resolveRouteBinding($value)
    {
        return $this->where('id', '=', $value)->first() ?? abort(404, 'User not found.');
    }

}
