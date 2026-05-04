<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    background: #ffffff;
    padding: 24pt;
}

/* ── LANDSCAPE CARD ─────────────────────────────────────────────────────────
   Layout constants
     card width        : 320pt  (compact, centered on A4)
     card padding      : 0pt    (padding lives inside the cells)
     column width      : 50% = 160pt each
     cell padding      : 12pt all sides
     left inner width  : 160 - 24 = 136pt  → QR image = 136pt (fills safely)
     right inner width : 160 - 24 = 136pt
     logo width        : 100pt  (centered in 136pt inner)
     card height       : driven by QR cell ≈ 136 + 24 = 160pt
   ────────────────────────────────────────────────────────────────────────── */
.ls-wrap {
    border: 2pt solid #8FA7B0;
    border-radius: 12pt;
    width: 320pt;
    margin: 0 auto 28pt;
    overflow: hidden;
}
.ls-inner {
    display: table;
    width: 100%;
}
.ls-left {
    display: table-cell;
    width: 50%;
    vertical-align: middle;
    padding: 12pt;
}
.ls-right {
    display: table-cell;
    width: 50%;
    vertical-align: middle;
    text-align: center;
    padding: 12pt;
}
.ls-qr {
    width: 136pt;
    height: auto;
    display: block;
    margin: 0 auto;
}
.ls-logo {
    width: 100pt;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* ── PORTRAIT CARD ── */
.pt-wrap {
    border: 2pt solid #8FA7B0;
    border-radius: 12pt;
    padding: 20pt 16pt;
    width: 220pt;
    margin: 0 auto;
    text-align: center;
}

/* ── SHARED ── */
.qr-img {
    width: 170pt;
    height: auto;
    display: block;
    margin: 0 auto;
}
.logo-img {
    width: 130pt;
    height: auto;
    display: block;
    margin: 0 auto;
}
.domain-text {
    font-size: 10pt;
    color: #706B62;
    margin-top: 10pt;
}
.code-text {
    font-size: 14pt;
    font-weight: bold;
    color: #2F2E2A;
    letter-spacing: 0.08em;
    margin-top: 3pt;
}
</style>
</head>
<body>

{{-- ── LANDSCAPE CARD ── --}}
<div class="ls-wrap">
    <div class="ls-inner">
        <div class="ls-left">
            <img class="ls-qr" src="data:image/png;base64,{{ $rawQrB64 }}" alt="QR-Code">
        </div>
        <div class="ls-right">
            @if ($logoB64)
                <img class="ls-logo" src="data:image/png;base64,{{ $logoB64 }}" alt="memorybook">
            @endif
            <p class="domain-text">memorybook.at</p>
            <p class="code-text">{{ $code }}</p>
        </div>
    </div>
</div>

{{-- ── PORTRAIT CARD ── --}}
<div class="pt-wrap">
    @if ($logoB64)
        <img class="logo-img" src="data:image/png;base64,{{ $logoB64 }}" alt="memorybook" style="margin-bottom: 14pt;">
    @endif
    <img class="qr-img" src="data:image/png;base64,{{ $rawQrB64 }}" alt="QR-Code">
    <p class="domain-text">memorybook.at</p>
    <p class="code-text">{{ $code }}</p>
</div>

</body>
</html>
