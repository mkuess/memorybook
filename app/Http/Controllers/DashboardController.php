<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $memoryPages = auth()->user()->memoryPages()
            ->whereNull('customer_removed_at')
            ->withCount('stories')
            ->with(['qrCode', 'orders' => fn ($q) => $q->where('status', 'paid')])
            ->latest()
            ->get();

        return view('dashboard', compact('memoryPages'));
    }
}
