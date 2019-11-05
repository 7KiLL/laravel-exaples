<?php


namespace App\Pipelines\Search\User;


use App\Pipelines\Filter;
use Illuminate\Support\Arr;
use ScoutElastic\Builders\SearchBuilder;

class Geo extends Filter
{

    protected $keys = [
        'current_city',
        'current_country',
        'current_continent',
        'home_city',
        'home_country',
        'home_continent',
        'city',
        'country',
        'continent'
    ];

    /**
     * @param SearchBuilder $request
     * @param \Closure $next
     * @return mixed|void
     */
    public function handle($request, \Closure $next)
    {
        if ($this->checkKeys()) {
            foreach (request($this->keys) as $key => $value) {
                if (!$value)
                    continue;

                if (in_array($key, ['city', 'country', 'continent'])) {
                    $request = $request
                        // ScoutBuilder doesn't have orWhere statement
//                        ->where("home_$key", '=', $value)
                        ->where("current_$key", '=', $value);
                }

                $request = $request->where($key, '=', $value);
            }
        }
        return $next($request);

    }
}
