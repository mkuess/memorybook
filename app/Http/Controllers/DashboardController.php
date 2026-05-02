<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $memoryPages = auth()->user()->memoryPages()->latest()->get();

        return view('dashboard', compact('memoryPages'));
    }
}
