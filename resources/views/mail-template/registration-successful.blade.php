<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking confirmed — Bruno Mars Live</title>
</head>
<body style="margin:0;background:#f7eee3;color:#35101b;font-family:Arial,Helvetica,sans-serif;">
    <div style="padding:32px 16px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;margin:0 auto;background:#fffaf3;border:1px solid #e4c990;border-radius:20px;overflow:hidden;">
            <tr>
                <td style="padding:28px 32px;background:#561526;color:#fffaf3;text-align:center;">
                    <div style="display:inline-block;width:42px;height:42px;line-height:42px;border-radius:50%;background:#d7ad62;color:#35101b;font-size:24px;">♪</div>
                    <h1 style="margin:14px 0 0;font-size:26px;letter-spacing:-.4px;">Bruno <span style="color:#e7bd72;">Mars</span> Live</h1>
                    <p style="margin:8px 0 0;color:#f3d9a1;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Official ticket confirmation</p>
                </td>
            </tr>
            <tr>
                <td style="padding:34px 32px 12px;">
                    <p style="margin:0;color:#a06b58;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">You're on the list</p>
                    <h2 style="margin:10px 0 8px;font-size:28px;line-height:1.15;">Your booking is confirmed, {{ $user_name }}!</h2>
                    <p style="margin:0;color:#735d5c;font-size:15px;line-height:1.6;">Get ready for a night of rhythm, energy, and unforgettable Bruno Mars moments.</p>
                </td>
            </tr>
            <tr>
                <td style="padding:20px 32px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#561526;border-radius:14px;color:#fffaf3;">
                        <tr><td style="padding:20px;">
                            <p style="margin:0;color:#e7bd72;font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;">Event details</p>
                            <h3 style="margin:10px 0 16px;font-size:21px;">{{ $purchase->concert->title }}</h3>
                            <p style="margin:7px 0;color:#f3d9a1;font-size:14px;">📅 <strong>Date:</strong> {{ $purchase->concert->concert_date->format('d F Y') }}</p>
                            <p style="margin:7px 0;color:#f3d9a1;font-size:14px;">🕒 <strong>Time:</strong> 18.00 - 22.00</p>
                            <p style="margin:7px 0;color:#f3d9a1;font-size:14px;">📍 <strong>Venue:</strong> Universitas Ciputra Surabaya, Indonesia</p>
                        </td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:0 32px 28px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;">
                        <tr><td colspan="2" style="padding:18px;text-align:center;background:#f3dfbd;border:1px dashed #c89548;border-radius:12px;"><p style="margin:0 0 7px;color:#735d5c;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;">Your booking code</p><strong style="color:#561526;font-size:25px;letter-spacing:3px;">{{ $purchase->confirmation_code }}</strong></td></tr>
                        <tr><td style="padding:10px 0;color:#735d5c;border-bottom:1px solid #eadbc7;">Seat category</td><td style="padding:10px 0;text-align:right;font-weight:bold;text-transform:capitalize;border-bottom:1px solid #eadbc7;">{{ $purchase->seat_type }}</td></tr>
                        <tr><td style="padding:10px 0;color:#735d5c;border-bottom:1px solid #eadbc7;">Tickets</td><td style="padding:10px 0;text-align:right;font-weight:bold;border-bottom:1px solid #eadbc7;">{{ $purchase->quantity }}</td></tr>
                        <tr><td style="padding:10px 0;color:#735d5c;border-bottom:1px solid #eadbc7;">Phone number</td><td style="padding:10px 0;text-align:right;font-weight:bold;border-bottom:1px solid #eadbc7;">{{ $purchase->phone }}</td></tr>
                        <tr><td style="padding:14px 0 0;color:#735d5c;font-weight:bold;">Total paid</td><td style="padding:14px 0 0;text-align:right;color:#9b642d;font-size:19px;font-weight:bold;">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td></tr>
                    </table>
                </td>
            </tr>
            @if ($purchase->notes)
                <tr>
                    <td style="padding:0 32px 28px;">
                        <div style="padding:16px 18px;background:#fff7e8;border-left:4px solid #c89548;border-radius:8px;color:#735d5c;font-size:14px;line-height:1.6;">
                            <strong style="color:#561526;">Your notes</strong><br>
                            {{ $purchase->notes }}
                        </div>
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding:22px 32px;background:#f3dfbd;text-align:center;color:#735d5c;font-size:13px;line-height:1.6;">
                    Please keep this email and your booking code for entry.<br>
                    We may contact you at {{ $purchase->phone }} regarding your booking.<br>
                    We can't wait to see you there.
                </td>
            </tr>
        </table>
        <p style="max-width:620px;margin:18px auto 0;text-align:center;color:#a4877d;font-size:12px;">© {{ date('Y') }} Bruno Mars Live · Official ticket registration</p>
    </div>
</body>
</html>
