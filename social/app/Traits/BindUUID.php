<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait BindUUID
{
    /**
     * Check for valid UUID before bind model to DI
     * @param mixed $value
     * @return Model|null
     */
    public function resolveRouteBinding($value)
    {
        if (Uuid::isValid($value))
            return parent::resolveRouteBinding($value);
    }
}
