<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers\API\V1';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
//        Route::fallback('App\Http\Controllers\API\V1\RedirectController@webRedirect');

    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group(
            [
                'prefix' => 'v1/',
                'middleware' => 'api',
                'namespace' => $this->namespace
            ], function () {
            require_once base_path('routes/api/v1/oauth.php');
            require_once base_path('routes/api/v1/auth.php');
            require_once base_path('routes/api/v1/miscellaneous.php');
            Route::middleware(['auth', 'sentry.user'])->group(function () {
                require_once base_path('routes/api/v1/users.php');
                require_once base_path('routes/api/v1/companies.php');
                require_once base_path('routes/api/v1/commentaries.php');
                require_once base_path('routes/api/v1/timelines.php');
                require_once base_path('routes/api/v1/geo.php');
                require_once base_path('routes/api/v1/friends.php');
                require_once base_path('routes/api/v1/items.php');
                require_once base_path('routes/api/v1/about.php');
                require_once base_path('routes/api/v1/filters.php');
                require_once base_path('routes/api/v1/scores.php');
                require_once base_path('routes/api/v1/assessments.php');
                require_once base_path('routes/api/v1/search.php');
            });
        });
    }
}
