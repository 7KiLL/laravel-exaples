<?php

namespace App\Providers;

use App\Models\About\Education;
use App\Models\About\Work;
use App\Models\Actors\Company;
use App\Models\Actors\User;
use App\Models\Attachments\Geo;
use App\Models\Attachments\Link;
use App\Models\Attachments\Picture;
use App\Models\Attachments\Video;
use App\Models\Common\Comment;
use App\Models\Common\Company\Event;
use App\Models\Common\Company\Item;
use App\Models\Common\Emoji;
use App\Models\Common\Score;
use App\Models\Common\SelfAssessment;
use App\Models\Common\Timeline;
use App\Models\Email\Invitation;
use App\Observers\About\EducationObserver;
use App\Observers\About\WorkObserver;
use App\Observers\Actors\CompanyObserver;
use App\Observers\Actors\UserObserver;
use App\Observers\Attachments\GeoObserver;
use App\Observers\Attachments\LinkObserver;
use App\Observers\Attachments\PictureObserver;
use App\Observers\Attachments\VideoObserver;
use App\Observers\Common\CommentObserver;
use App\Observers\Common\Company\ItemObserver;
use App\Observers\Common\EmojiObserver;
use App\Observers\Common\ScoreObserver;
use App\Observers\Common\SelfAssessmentObserver;
use App\Observers\Common\TimelineObserver;
use App\Observers\Email\InvitationObserver;
use Bezhanov\Faker\Provider\Educator;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->extend(Generator::class, function(Generator $faker) {
             $faker->addProvider(new Educator($faker));
            return $faker;
        });

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

            DB::listen(function ($query) {
                $query->sql;
                $query->bindings;
                $query->time;
                dump("[$query->time ms] $query->sql");
            });

        }


        /**
         * Actors and semi-actors
         */
        User::observe(UserObserver::class);
        Company::observe(CompanyObserver::class);
        /**
         * Common
         */
        Timeline::observe(TimelineObserver::class);
        Emoji::observe(EmojiObserver::class);
        Comment::observe(CommentObserver::class);
        Score::observe(ScoreObserver::class);
        SelfAssessment::observe(SelfAssessmentObserver::class);
        /**
         * Attachments
         */
        Picture::observe(PictureObserver::class);
        Video::observe(VideoObserver::class);
        Link::observe(LinkObserver::class);
        Geo::observe(GeoObserver::class);
        /**
         * Stuff
         */
        Invitation::observe(InvitationObserver::class);
        /**
         * About
         */
        Education::observe(EducationObserver::class);
        Work::observe(WorkObserver::class);
        /**
         * Item
         */
        Item::observe(ItemObserver::class);
        /**
         * Application morph map
         */
        Relation::morphMap([
            'timelines' => Timeline::class,
            'companies' => Company::class,
            'users' => User::class,
            'comments' => Comment::class,
            'items' => Item::class,
            'events' => Event::class
        ]);
        /**
         * Flatten an array while keeping it's keys, even non-incremental numeric ones, in tact.
         *
         * Unless $dotNotification is set to true, if nested keys are the same as any
         * parent ones, the nested ones will supersede them.
         *
         * @param int $depth How many levels deep to flatten the array
         * @param bool $dotNotation Maintain all parent keys in dot notation
         */
        Collection::macro('flattenKeepKeys', function ($depth = 1, $dotNotation = false) {
            if ($depth) {
                $newArray = [];
                foreach ($this->items as $parentKey => $value) {
                    if (is_array($value)) {
                        $valueKeys = array_keys($value);
                        foreach ($valueKeys as $key) {
                            $subValue = $value[$key];
                            $newKey = $key;
                            if ($dotNotation) {
                                $newKey = "$parentKey.$key";
                                if ($dotNotation !== true) {
                                    $newKey = "$dotNotation.$newKey";
                                }

                                if (is_array($value[$key])) {
                                    $subValue = collect($value[$key])->flattenKeepKeys($depth - 1, $newKey)->toArray();
                                }
                            }
                            $newArray[$newKey] = $subValue;
                        }
                    } else {
                        $newArray[$parentKey] = $value;
                    }
                }

                $this->items = collect($newArray)->flattenKeepKeys(--$depth, $dotNotation)->toArray();
            }

            return collect($this->items);
        });

    }
}
