<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify OTP - Super Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 420px; margin: 48px auto; background: #fff; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .header { padding: 20px; text-align: center; color: #2563eb; }
        .content { padding: 20px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 14px; color: #374151; margin-bottom: 6px; }
        input[type="text"] { width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 18px; letter-spacing: 6px; text-align: center; }
        .btn { width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .message { padding: 10px 12px; border-radius: 8px; font-size: 14px; margin-bottom: 12px; }
        .error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .status { background: #ecfeff; color: #0e7490; border: 1px solid #a5f3fc; }
        .note { font-size: 12px; color: #6b7280; text-align: center; margin-top: 10px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('otp');
            if (input) input.focus();
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        });
    </script>
    </head>
<body>
    <div class="container">
        <div class="header">
            <h2>Super Admin OTP Verification</h2>
            <p>Enter the 6-digit code sent to your MS365 email</p>
        </div>
        <div class="content">
            @if(session('status'))
                <div class="message status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="message error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('superadmin.otp.verify') }}">
                @csrf
                <div class="form-group">
                    <label for="otp">One-Time Password</label>
                    <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="••••••" required>
                </div>
                <button type="submit" class="btn">Verify and Continue</button>
            </form>
            <p class="note">Code expires in 10 minutes. Maximum 5 attempts.</p>
        </div>
    </div>
</body>
</html>


