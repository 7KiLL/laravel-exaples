<?php


namespace App\Pipelines;


use Illuminate\Support\Str;

abstract class Pipe
{

    public function handle($request, \Closure $next)
    {
        return next($request);
    }

    public function hasOwnRequest()
    {
        $className = $this->getName();
        if (request()->has(Str::snake($className))) {
            return request()->get($className);
        } else {
            return false;
        }
    }

    protected function getName(): string
    {
        return Str::snake(class_basename($this));
    }
}
