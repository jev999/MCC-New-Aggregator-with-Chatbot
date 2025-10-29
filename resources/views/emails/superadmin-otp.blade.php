<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Super Admin OTP Code</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .card { max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; }
        .title { color: #2563eb; }
        .code { font-size: 28px; font-weight: bold; letter-spacing: 6px; background: #f3f4f6; padding: 12px 16px; border-radius: 8px; text-align: center; }
        .muted { color: #6b7280; font-size: 14px; }
    </style>
    </head>
<body>
    <div class="card">
        <h2 class="title">MCC News Aggregator - Super Admin OTP</h2>
        <p>Use the one-time password below to complete your login:</p>
        <div class="code">{{ $code }}</div>
        <p class="muted">This code expires in {{ $expiresInMinutes }} minutes. If you did not request this code, you can ignore this email.</p>
    </div>
</body>
</html>


