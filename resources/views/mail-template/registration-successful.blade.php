@php
    $seatLabel = config("tickets.seats.{$purchase->seat_type}.label", ucfirst($purchase->seat_type));
    $display = "'Bricolage Grotesque', 'Helvetica Neue', Arial, sans-serif";
    $body = "'Instrument Sans', 'Helvetica Neue', Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>Your booking — Bruno Mars Live</title>
</head>
<body style="margin:0;padding:0;background-color:#1d0811;color:#fff8ed;font-family:{{ $body }};">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Booking code {{ $purchase->confirmation_code }}: {{ $purchase->quantity }} × {{ $seatLabel }}</div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#1d0811" style="background-color:#1d0811;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;">
                    <tr>
                        <td style="padding:0 4px 28px;font-family:{{ $display }};font-size:20px;font-weight:800;color:#fff8ed;">Bruno <span style="color:#f3d9a1;">Mars</span></td>
                    </tr>
                    <tr>
                        <td style="padding:0 4px 24px;">
                            <h1 style="margin:0;font-family:{{ $display }};font-size:30px;line-height:1.15;font-weight:800;color:#fff8ed;">Your booking is confirmed, {{ $user_name }}!</h1>
                            <p style="margin:12px 0 0;font-size:15px;line-height:1.6;color:#d9c3b4;">Keep this email. You will need your booking code at the entrance.</p>
                        </td>
                    </tr>

                    {{-- Kartu tiket: sama dengan kartu di halaman utama. --}}
                    <tr>
                        <td bgcolor="#f3d9a1" style="background-color:#f3d9a1;border-radius:16px;color:#31101a;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:26px 28px 22px;color:#31101a;">
                                        <p style="margin:0;font-size:14px;font-weight:700;color:#31101a;">{{ $purchase->concert->title }}</p>
                                        <p style="margin:10px 0 0;font-family:{{ $display }};font-size:30px;line-height:1.15;font-weight:800;color:#31101a;">{{ $purchase->concert->concert_date->format('j F Y') }}</p>
                                        <p style="margin:4px 0 0;font-size:16px;font-weight:700;color:#31101a;">18.00 - 22.00</p>
                                        <p style="margin:12px 0 0;font-size:14px;line-height:1.5;color:#31101a;">{{ $purchase->concert->venue }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px;"><div style="border-top:2px dashed #a8865a;font-size:0;line-height:0;">&nbsp;</div></td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:20px 28px 26px;color:#31101a;">
                                        <p style="margin:0;font-size:13px;color:#31101a;">Your booking code</p>
                                        <p style="margin:6px 0 0;font-family:'SFMono-Regular',Consolas,'Courier New',monospace;font-size:30px;font-weight:700;letter-spacing:4px;color:#31101a;">{{ $purchase->confirmation_code }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 4px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:14px;">
                                @foreach (['Seat category' => $seatLabel, 'Tickets' => $purchase->quantity, 'Phone number' => $purchase->phone] as $label => $value)
                                    <tr>
                                        <td style="padding:12px 0;border-bottom:1px solid #6a3a3f;color:#d9c3b4;">{{ $label }}</td>
                                        <td align="right" style="padding:12px 0;border-bottom:1px solid #6a3a3f;font-weight:700;color:#fff8ed;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:16px 0 0;font-weight:700;color:#fff8ed;">Total</td>
                                    <td align="right" style="padding:16px 0 0;font-family:{{ $display }};font-size:24px;font-weight:800;color:#f3d9a1;">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:28px 4px 0;">
                            <a href="{{ route('tickets.index') }}" style="display:block;background-color:#d7ad62;border-radius:999px;padding:15px 24px;font-size:15px;font-weight:700;color:#31101a;text-decoration:none;">View My Tickets</a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:28px 4px 0;font-size:12px;line-height:1.7;color:#d9c3b4;">
                            We may contact you at {{ $purchase->phone }} about your booking.<br>
                            © {{ date('Y') }} Bruno Mars Live. Official ticket registration.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
