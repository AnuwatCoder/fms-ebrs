<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardQueryService $dashboard): View
    {
        return view('dashboard', $dashboard->for($request->user()));
    }
}
