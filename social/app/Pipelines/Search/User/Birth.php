<?php


namespace App\Pipelines\Search\User;


use App\Pipelines\Filter;

class Birth extends Filter
{
    protected $keys = [
        'birth_from',
        'birth_to'
    ];

    public function handle($request, \Closure $next)
    {
        if (!request()->hasAny($this->keys))
            return $next($request);


        if ($this->hasBoth()) {
            $request = $request->whereBetween($this->getName(), request($this->keys));
        } else {
            $request = $this->keys[0] === $this->availableKey()
                ? $request->where($this->getName(), '>=', request($this->keys[0]))
                : $request->where($this->getName(), '<=', request($this->keys[1]));
        }

        return $next($request);
    }

    private function hasBoth()
    {
        return request($this->keys[0]) && request($this->keys[1]);
    }

    private function availableKey()
    {
        return request($this->keys[0]) ? $this->keys[0] : $this->keys[1];
    }

}
