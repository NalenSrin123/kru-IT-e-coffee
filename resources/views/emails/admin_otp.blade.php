<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OTP Code</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <h2 style="color: #333;">E-Coffee OTP Code</h2>
    <p>This is your OTP code for logging into the E-Coffee Management System. This code will expire in <strong>3 minutes</strong>.</p>
    <div style="text-align: center; margin: 20px 0;">
        <span style="font-size: 24px; font-weight: bold; background: #f4f4f4; padding: 10px 20px; border-radius: 5px; letter-spacing: 2px;">
            {{ $otpCode }}
        </span>
    </div>
    <p style="color: #777; font-size: 12px;">If you did not request this code, please ignore this email.</p>
</div>
</body>
</html>