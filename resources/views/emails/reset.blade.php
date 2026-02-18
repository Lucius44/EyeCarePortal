<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { margin: 0; padding: 0; background-color: #F3F4F6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #0F172A; padding: 30px; text-align: center; }
        .logo { max-height: 50px; }
        .content { padding: 40px; color: #334155; line-height: 1.6; }
        .btn-container { text-align: center; margin: 30px 0; }
        /* UPDATED: Dark Navy Background */
        .btn { display: inline-block; background-color: #0F172A; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; }
        .btn:hover { background-color: #1E293B; } /* Slightly lighter navy on hover */
        .footer { background-color: #F8FAFC; padding: 20px; text-align: center; font-size: 12px; color: #94A3B8; }
        .link-text { color: #3B82F6; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header with Logo --}}
        <div class="header">
            {{-- UPDATED: Using embed() to attach the image file directly --}}
            <img src="{{ $message->embed(public_path('images/sixeyes.png')) }}" alt="ClearOptics Logo" class="logo">
        </div>

        {{-- Body Content --}}
        <div class="content">
            <h2 style="color: #0F172A; margin-top: 0;">Reset Password Request</h2>
            <p>
                Hello {{ $user->first_name }},
            </p>
            <p>
                You are receiving this email because we received a password reset request for your account.
            </p>
            
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Reset Password</a>
            </div>

            <p>
                This password reset link will expire in 60 minutes.
            </p>
            <p>
                If you did not request a password reset, no further action is required.
            </p>

            <p style="margin-top: 30px;">
                Best Regards,<br>
                <strong>The ClearOptics Team</strong>
            </p>

            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 30px 0;">

            <p style="font-size: 12px; color: #64748B;">
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
                <br>
                <a href="{{ $url }}" class="link-text">{{ $url }}</a>
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            &copy; {{ date('Y') }} ClearOptics. All rights reserved.
        </div>
    </div>
</body>
</html>