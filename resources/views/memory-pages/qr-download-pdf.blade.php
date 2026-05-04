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

/* ── LANDSCAPE CARD ── */
.ls-wrap {
    border: 2pt solid #8FA7B0;
    border-radius: 12pt;
    padding: 16pt;
    margin-bottom: 28pt;
}
.ls-inner {
    display: table;
    width: 100%;
}
.ls-left {
    display: table-cell;
    width: 55%;
    vertical-align: middle;
}
.ls-right {
    display: table-cell;
    width: 45%;
    vertical-align: middle;
    text-align: center;
    padding-left: 12pt;
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
            <img class="qr-img" src="data:image/png;base64,{{ $rawQrB64 }}" alt="QR-Code">
        </div>
        <div class="ls-right">
            @if ($logoB64)
                <img class="logo-img" src="data:image/png;base64,{{ $logoB64 }}" alt="memorybook">
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
