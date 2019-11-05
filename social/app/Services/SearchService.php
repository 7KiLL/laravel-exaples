<?php


namespace App\Services;


use Composer\Autoload\ClassMapGenerator;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Searchable;

class SearchService
{

    /**
     * Pipeline filters to pass search request
     * @var array $filters
     */
    private $filters = [];


    /** @var Model|Searchable */
    private $model;

    /**
     * @var Builder
     */
    private $query;

    public function __construct()
    {
    }

    /**
     * Build the instance using working model
     * @param Model $model
     */
    public function make(Model $model)
    {
        // Add filters according to the model passed
        $filtersDir = app_path('Pipelines/Search/' . class_basename($model));
        $filterList = ClassMapGenerator::createMap($filtersDir);
        $this->addFilters(array_keys($filterList));


        $this->model = $model;

    }

    public function query(string $query)
    {
        // Pass through all filters. We must generate ScoutSearchBuilder conditions...
        $wheres = $this->pipeline($this->model::search(''))->wheres;
        // ...to put them into raw query builder near the fuzzy search
        $model = $this->model::search($query, function (Client $client, $query, $params) use ($wheres) {
            $params['body']['query']['bool']['filter'] = $wheres['must'];

            $params['body']['query']['bool']['must']['multi_match'] = [
                'query' => $query,
                'fuzziness' => 1,
                'boost' => 0,
                'prefix_length' => 2,
                'fields' => ['*name'],

            ];
//            dd($params);
            return $client->search($params);

        });
        return $model;
    }

    /**
     * @param string|array $filters
     * @return SearchService
     */
    public function addFilters(...$filters): SearchService
    {
        $wrap = Arr::wrap($filters);
        $this->filters = array_merge($this->filters, array_unique(Arr::flatten($wrap)));
        return $this;
    }

    private function pipeline(SearchBuilder $query): SearchBuilder
    {
        return (app(Pipeline::class))
            ->send($query)
            ->through($this->filters)
            ->thenReturn();
    }
}
