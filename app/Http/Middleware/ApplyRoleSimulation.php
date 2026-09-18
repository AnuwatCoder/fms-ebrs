<?php

namespace App\Http\Middleware;

use App\Services\Authorization\RoleSimulationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyRoleSimulation
{
    public function __construct(private RoleSimulationService $roleSimulation) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->roleSimulation->apply($request);

        return $next($request);
    }
}
