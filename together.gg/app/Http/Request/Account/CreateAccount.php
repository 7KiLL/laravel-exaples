<?php

namespace App\Http\Requests\Account;

use App\Models\Entry;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccount extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param User $user
     * @return bool
     */
    public function authorize(User $user)
    {
        return !$user->account()->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'server' => [
                'required',
                Rule::in(Entry::SERVERS)
            ],
            'name' => [
                'required',
                'min:3',
                'max:16'
            ]
        ];
    }

    public function messages()
    {
        return [
            'server.in' => 'Selected server is not in the list.',
        ];
    }
}
