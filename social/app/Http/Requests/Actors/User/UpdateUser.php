<?php

namespace App\Http\Requests\Actors\User;

use App\Models\Actors\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUser extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var User $user */
        $user = \Auth::user();
        return [
            'email' => ['sometimes','required_without:phone', 'email', Rule::unique('users','email')->ignoreModel($user)],
            'phone' => ['sometimes', 'required_without:email','integer', Rule::unique('users','phone')->ignoreModel($user)],
            'first_name' => 'sometimes|string|min:3|max:255',
            'last_name' => 'sometimes|string|min:3|max:255',
            'gender' => ['sometimes', Rule::in(['male', 'female'])],
            'nation_code' => 'sometimes|exists:nationalities,alpha_2_code',
            'religion' => ['sometimes', Rule::in(['christian', 'muslim', 'hindus', 'jews', 'sikhs', 'buddhist', 'other'])],
            'marital' => ['sometimes', Rule::in(['single', 'married', 'widowed', 'divorced'])],
            'home_city' => 'sometimes|exists:world_cities,id',
            'current_city' => 'sometimes|exists:world_cities,id',
            'birth' => 'sometimes|date'
        ];
    }
}
