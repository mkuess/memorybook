<?php

namespace App\Http\Controllers;

use App\Mail\NewOrderNotification;
use App\Models\MemoryPage;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        return view('checkout.create', compact('memoryPage'));
    }

    public function store(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $validated = $request->validate([
            'package'             => ['required', 'in:basic,plaque'],
            'billing_name'        => ['required', 'string', 'max:255'],
            'billing_email'       => ['required', 'email', 'max:255'],
            'billing_address'     => ['required', 'string', 'max:500'],
            'billing_postal_code' => ['required', 'string', 'max:20'],
            'billing_city'        => ['required', 'string', 'max:255'],
            'billing_country'     => ['required', 'string', 'max:255'],
            'consent'             => ['accepted'],
        ]);

        $order = Order::create([
            'user_id'             => $request->user()->id,
            'memory_page_id'      => $memoryPage->id,
            'package'             => $validated['package'],
            'billing_name'        => $validated['billing_name'],
            'billing_email'       => $validated['billing_email'],
            'billing_address'     => $validated['billing_address'],
            'billing_postal_code' => $validated['billing_postal_code'],
            'billing_city'        => $validated['billing_city'],
            'billing_country'     => $validated['billing_country'],
            'consent_confirmed_at' => now(),
        ]);

        $this->notifyAdmin($order);

        return redirect()
            ->route('memory-pages.checkout.confirmed', $memoryPage)
            ->with('order_id', $order->id);
    }

    public function confirmed(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        return view('checkout.confirmed', compact('memoryPage'));
    }

    private function notifyAdmin(Order $order): void
    {
        $adminEmail = config('mail.admin_email') ?? config('mail.from.address');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new NewOrderNotification($order));
        } catch (\Throwable $e) {
            Log::warning('Admin order notification could not be sent.', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
