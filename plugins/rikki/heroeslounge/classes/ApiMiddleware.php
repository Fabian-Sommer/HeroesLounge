<?php
namespace Rikki\Heroeslounge\Classes;

use Closure;
use Response;
use Rikki\Heroeslounge\Models\ApiKeys;
use Log;

/**
 * Class ApiMiddleware
 *
 * @package Rikki\Heroeslounge\Classes
 */
class ApiMiddleware
{
    /**
     * Check if the key has hit its quota.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = $request->header('key');
        
        if (ApiKeys::getAccessForKey($key)) {
            return $next($request);
        } else {
            return Response::make('Access Denied', 403);
        }
    }
}
