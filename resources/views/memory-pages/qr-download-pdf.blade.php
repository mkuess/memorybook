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
   Layout constants:
     card width  : 340pt  (compact, centered on A4)
     card padding: 10pt vertical / 12pt horizontal
     QR column   : 42% of inner width  ≈ 132pt → QR image 118pt
     gap         : 8pt  (padding-right on left cell only)
     right column: 58% of inner width
     logo width  : 100pt
   ────────────────────────────────────────────────────────────────────────── */
.ls-wrap {
    border: 2pt solid #8FA7B0;
    border-radius: 12pt;
    padding: 10pt 12pt;
    width: 340pt;
    margin: 0 auto 28pt;
}
.ls-inner {
    display: table;
    width: 100%;
}
.ls-left {
    display: table-cell;
    width: 42%;
    vertical-align: middle;
    padding-right: 8pt;
}
.ls-right {
    display: table-cell;
    width: 58%;
    vertical-align: middle;
    text-align: center;
}
.ls-qr {
    width: 118pt;
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
