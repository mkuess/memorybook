<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class MemoryPageQrController extends Controller
{
    public function show(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $qr = $memoryPage->qrCode;

        return view('memory-pages.qr-code', compact('memoryPage', 'qr'));
    }
}
