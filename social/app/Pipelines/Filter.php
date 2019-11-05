<?php


namespace App\Pipelines;


use Illuminate\Database\Eloquent\Builder;

abstract class Filter extends Pipe
{
    protected $keys = [];

    /**
     * @param Builder $request
     * @param \Closure $next
     * @return mixed|void
     */
    public function handle($request, \Closure $next)
    {
        if (!$req = $this->hasOwnRequest())
            return $next($request);

        $request = $request->where($this->getName(), $req);
        return $next($request);
    }

    protected function checkKeys() {
        return request()->hasAny($this->keys);
    }

}
