<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><title>Neue Bestellung</title></head>
<body style="font-family:sans-serif;color:#2F2E2A;padding:24px;">
    <h2>Neue Bestellung eingegangen (#{{ $order->id }})</h2>
    <table style="border-collapse:collapse;width:100%;max-width:500px;">
        <tr><td style="padding:6px 0;font-weight:600;">Erinnerungsseite</td><td style="padding:6px 12px;">{{ $order->memoryPage->person_name ?? '–' }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">Paket</td><td style="padding:6px 12px;">{{ \App\Models\Order::$packages[$order->package] ?? $order->package }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">Name</td><td style="padding:6px 12px;">{{ $order->billing_name }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">E-Mail</td><td style="padding:6px 12px;">{{ $order->billing_email }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">Adresse</td><td style="padding:6px 12px;">{{ $order->billing_address }}, {{ $order->billing_postal_code }} {{ $order->billing_city }}, {{ $order->billing_country }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">Status</td><td style="padding:6px 12px;">{{ $order->status }}</td></tr>
        <tr><td style="padding:6px 0;font-weight:600;">Bestellt am</td><td style="padding:6px 12px;">{{ $order->created_at->format('d.m.Y H:i') }}</td></tr>
    </table>
</body>
</html>
