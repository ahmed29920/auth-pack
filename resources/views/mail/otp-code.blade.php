@extends('kango-auth::mail.layout')

@section('content')
    @if ($recipientName)
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#334155;">
            {{ __('kango-auth::auth.mail.greeting', ['name' => $recipientName]) }}
        </p>
    @endif

    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#475569;">
        {{ __('kango-auth::auth.mail.otp_intro', ['purpose' => $purposeLabel]) }}
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;">
        <tr>
            <td align="center" style="background-color:#f8fafc;border:2px dashed #c7d2fe;border-radius:12px;padding:24px;">
                <span style="font-size:32px;font-weight:700;letter-spacing:8px;color:#4f46e5;font-family:ui-monospace,monospace;">{{ $code }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#64748b;">
        {{ __('kango-auth::auth.mail.otp_expires', ['time' => $expiresAt->timezone(config('app.timezone'))->format('M j, Y g:i A')]) }}
    </p>

    <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#94a3b8;">
        {{ __('kango-auth::auth.mail.otp_security_note') }}
    </p>
@endsection
