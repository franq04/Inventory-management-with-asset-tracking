<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQS Password Reset</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #f5f7fb;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #f5f7fb; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; background: #ffffff; border: 1px solid #e6e9ef; border-radius: 14px; overflow: hidden;">
                    <tr>
                        <td style="padding: 20px 24px 8px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="left" style="vertical-align: middle;">
                                        <img src="{{ $message->embed(public_path('images/pqslogo.png')) }}" width="48" height="48" alt="PQS Logo" style="display: block; border: 0; outline: none; text-decoration: none;">
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <img src="{{ $message->embed(public_path('images/bpi-logo.png')) }}" width="48" height="48" alt="BPI Logo" style="display: block; border: 0; outline: none; text-decoration: none;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 24px 16px;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.12em;">Inventory Management with Asset Tracking System</p>
                            <h2 style="margin: 8px 0 6px; color: #1a3a2d; font-size: 22px;">Password Reset Request</h2>
                            <p style="margin: 0; color: #4b5563; font-size: 14px;">Bureau of Plant Industry</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 24px 12px;">
                            <p style="margin: 0 0 10px; color: #374151; font-size: 14px;">Hello {{ $username }},</p>
                            <p style="margin: 0; color: #4b5563; font-size: 14px;">We received a request to reset your account password. If this was you, use the button below.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 24px 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #f9fafb; border: 1px solid #e6e9ef; border-radius: 10px;">
                                <tr>
                                    <td style="padding: 12px 14px;">
                                        <p style="margin: 0 0 6px; color: #374151; font-size: 13px;"><strong>Username:</strong> {{ $username }}</p>
                                        <p style="margin: 0 0 6px; color: #374151; font-size: 13px;"><strong>Recovery Email:</strong> {{ $email }}</p>
                                        <p style="margin: 0 0 6px; color: #374151; font-size: 13px;"><strong>Requested At:</strong> {{ $requestedAt }}</p>
                                        @if (!empty($requestIp))
                                            <p style="margin: 0; color: #374151; font-size: 13px;"><strong>Request IP:</strong> {{ $requestIp }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding: 0 24px 20px;">
                            <a href="{{ $resetUrl }}" style="display: inline-block; background: #1a3a2d; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: 600; font-size: 14px;">Reset Password</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 24px 24px;">
                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 13px;">This link expires in 60 minutes.</p>
                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 13px;">If you did not request a password reset, please ignore this email.</p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">If the button does not work, copy and paste this link into your browser:</p>
                            <p style="margin: 6px 0 0; color: #1f2937; font-size: 12px; word-break: break-all;">{{ $resetUrl }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
