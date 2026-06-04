<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Documents Received</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:24px;background:#0d1b3d;">
                            @if(!empty($logo))
                                <img src="{{ $logo }}" alt="{{ $business_name ?? 'Allons-Z' }}" style="max-height:64px;display:block;">
                            @else
                                <div style="color:#fff;font-size:22px;font-weight:bold;">{{ $business_name ?? 'Allons-Z' }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 16px 32px;">
                            <h2 style="margin:0 0 12px 0;color:#0d1b3d;">Hi {{ $name ?? 'there' }},</h2>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:22px;">
                                We have received your KYC documents. Thank you for completing this step.
                            </p>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:22px;">
                                Your documents are now with our verification team. Your KYC step is marked as complete on the app.
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 16px 0;border:1px solid #e3e6ec;border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 16px;font-size:14px;">
                                        <strong>Submission ID:</strong> {{ $submission_id }}<br>
                                        <strong>Submitted at:</strong> {{ $submitted_at }}<br>
                                        @if(!empty($referal_code))
                                            <strong>Referral code:</strong> {{ $referal_code }}<br>
                                        @endif
                                        <strong>Documents received:</strong>
                                        <ul style="margin:8px 0 0 18px;padding:0;">
                                            <li>PAN Card</li>
                                            <li>Aadhar Card (front)</li>
                                            <li>Aadhar Card (back)</li>
                                            <li>Bank Details</li>
                                            @if(!empty($has_transaction_details))
                                                <li>Transaction / Other docs</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px 0;font-size:14px;line-height:22px;color:#444;">
                                If anything looks incorrect, please reply to this email and our team will assist you.
                            </p>
                            <p style="margin:24px 0 0 0;font-size:15px;line-height:22px;">Thank you,</p>
                            <p style="margin:0;font-size:15px;line-height:22px;"><strong>{{ $business_name ?? 'Allons-Z Team' }}</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 24px 32px;border-top:1px solid #eef0f4;font-size:12px;color:#7b8290;">
                            This is an automated message. For queries, contact
                            @if(!empty($email_support))
                                <a href="mailto:{{ $email_support }}" style="color:#0d1b3d;">{{ $email_support }}</a>.
                            @else
                                our support team.
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
