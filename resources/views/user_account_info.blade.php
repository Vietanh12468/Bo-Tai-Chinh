<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>User Account Info</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f8fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 20px;
            color: #111827;
            margin-bottom: 16px;
        }

        p {
            margin: 8px 0;
        }

        .info-box {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 12px;
        }

        .footer {
            font-size: 13px;
            color: #6b7280;
            margin-top: 24px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to Our Platform!</h1>

        <p>Dear User,</p>

        <p>Your account has been created successfully. Below are your login details:</p>

        <div class="info-box">
            <p><strong>Phone:</strong> {{ $phone ?? 'N/A' }}</p>
            <p><strong>Password:</strong> {{ $password ?? 'N/A' }}</p>
        </div>

        <p>You can now log in using the credentials above. For security reasons, please change your password after your first login.</p>

        <div class="footer">
            <p>Thank you,<br>The Support Team</p>
        </div>
    </div>
</body>

</html>