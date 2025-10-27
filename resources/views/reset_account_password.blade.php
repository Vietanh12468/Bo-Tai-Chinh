<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
            padding: 40px;
            color: #333;
        }
        .email-container {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            padding: 24px 32px;
        }
        .email-header {
            text-align: center;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .email-header h2 {
            color: #1a73e8;
            margin: 0;
        }
        .otp-code {
            display: inline-block;
            background-color: #1a73e8;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 6px;
            padding: 12px 24px;
            border-radius: 8px;
            margin: 16px 0;
        }
        .footer {
            text-align: center;
            color: #777;
            font-size: 13px;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Password Reset Request</h2>
        </div>

        <p>Hi {{ $user->name ?? 'User' }},</p>

        <p>We received a request to reset your password. Use the OTP code below to continue the process:</p>

        <div style="text-align:center;">
            <div class="otp-code">{{ $otp }}</div>
        </div>

        <p>This OTP is valid for <strong>10 minutes</strong>. If you did not request a password reset, please ignore this email.</p>

        <p>Thanks,<br>The {{ config('app.name') }} Team</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
