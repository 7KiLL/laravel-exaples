<?php

namespace App\Models\Search;

use App\Indices\InlineIndexConfigurator;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

/**
 * App\Models\Search\Inline
 *
 * @property string|null $id
 * @property string|null $title
 * @property string|null $type
 * @property \ScoutElastic\Highlight|null $highlight
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Search\Inline whereType($value)
 * @mixin \Eloquent
 */
class Inline extends Model
{
    use Searchable;
    /** SQL view */
    protected $table = 'inline_search';

    protected $casts = ['id' => 'string'];
    protected $mapping = [
        'properties' => [
            'title' => [
                "analyzer" => "autocomplete",
                "search_analyzer" => "standard",
                'type' => 'text',
//                'fields' => [
//                    'raw' => [
//                        'type' => 'keyword',
//                    ]
//                ]
            ],
        ]
    ];

    protected $indexConfigurator = InlineIndexConfigurator::class;

    public function toSearchableArray()
    {
        return $this->toArray();
    }
}
