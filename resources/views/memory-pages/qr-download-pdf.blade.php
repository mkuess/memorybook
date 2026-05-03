<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        background: #ffffff;
        margin: 0;
        padding: 0;
        text-align: center;
    }
    .card {
        padding: 60px 40px 50px;
    }
    img {
        width: 220px;
        height: 220px;
        display: block;
        margin: 0 auto;
    }
    .domain {
        margin-top: 16px;
        font-size: 13pt;
        color: #706B62;
    }
    .code {
        margin-top: 5px;
        font-size: 17pt;
        font-weight: bold;
        color: #2F2E2A;
        letter-spacing: 0.12em;
    }
</style>
</head>
<body>
<div class="card">
    <img src="data:image/png;base64,{{ $qrB64 }}" alt="QR-Code">
    <p class="domain">memorybook.com</p>
    <p class="code">{{ $code }}</p>
</div>
</body>
</html>
