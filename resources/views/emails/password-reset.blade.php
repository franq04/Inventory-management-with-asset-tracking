<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f7fb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e6e9ef;">
        <tr>
            <td>
                <h2 style="margin: 0 0 8px; color: #1a3a2d;">Password Reset</h2>
                <p style="margin: 0 0 16px; color: #4b5563;">Hello {{ $username }},</p>
                <p style="margin: 0 0 16px; color: #4b5563;">We received a request to reset your password. Click the button below to continue.</p>
                <p style="margin: 0 0 20px;">
                    <a href="{{ $resetUrl }}" style="display: inline-block; background: #1a3a2d; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: 600;">Reset Password</a>
                </p>
                <p style="margin: 0 0 16px; color: #6b7280; font-size: 13px;">If you did not request a password reset, you can ignore this email.</p>
                <p style="margin: 0; color: #6b7280; font-size: 13px;">This link expires in 60 minutes.</p>
            </td>
        </tr>
    </table>
</body>
</html>
