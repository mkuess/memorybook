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
        width: 320px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
</style>
</head>
<body>
<div class="card">
    <img src="data:image/png;base64,{{ $qrB64 }}" alt="QR-Code">
</div>
</body>
</html>
