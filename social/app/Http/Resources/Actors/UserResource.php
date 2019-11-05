<?php

namespace App\Http\Resources\Actors;

use App\Http\Resources\About\EducationResource;
use App\Http\Resources\About\WorkResource;
use App\Http\Resources\Common\ScoreResource;
use App\Http\Resources\Common\ScoreStatResource;
use App\Http\Resources\Miscellaneous\RoleResource;
use App\Models\Actors\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        /** @var User|JsonResource $this */
        return [
            'id' => $this->id,
            'email' => $this->when(Auth::id() === $this->id, $this->email),
            'email_verified_at' => $this->email_verified_at,
            'avatar' => $this->avatar,
            'header' => $this->header,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'religion' => $this->religion,
            'nation_code' => $this->nation_code,
            'marital' => $this->marital,
            'score' => (int)$this->score,
            'is_completed_self_assessment' => $this->is_completed_self_assessment,
            'stats' => ScoreResource::make($this->whenLoaded('stats')),
            'geo' => [
                'home_city' => $this->home_city,
                'home_country' => $this->home_country,
                'home_continent' => $this->home_continent,
                'current_city' => $this->current_city,
                'current_country' => $this->current_country,
                'current_continent' => $this->current_continent,
            ],

            $this->mergeWhen(\Auth::id() !== $this->id, [
                'friend' => FriendResource::make(optional(Auth::user()->getFriendship($this->resource))->load('groups'))
            ]),

            'birth' => $this->birth,
            'roles' => $this->when(Auth::user()->hasAnyRole(['su', 'admin', 'moderator']), RoleResource::collection($this->roles)),
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'educations' => EducationResource::collection($this->whenLoaded('educations')),
            'works' => WorkResource::collection($this->whenLoaded('works')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
