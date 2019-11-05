<?php

namespace App\Rules\Timeline;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class Picture implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value instanceof UploadedFile) {
            if (in_array($value->getMimeType(), ['image/png', 'image/jpg', 'image/jpeg'])
                && $value->getSize() < 200000) {
                return true;
            }
        } elseif (isset($value['id'])) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Given image is not valid.';
    }
}
