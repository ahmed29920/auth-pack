@extends('laravel-auth-kit::mail.layout')

@section('content')
    @if ($recipientName)
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#334155;">
            {{ __('laravel-auth-kit::auth.mail.greeting', ['name' => $recipientName]) }}
        </p>
    @endif

    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#475569;">
        {{ __('laravel-auth-kit::auth.mail.reset_intro') }}
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0;">
        <tr>
            <td style="border-radius:10px;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);">
                <a href="{{ $resetUrl }}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
                    {{ __('laravel-auth-kit::auth.mail.reset_button') }}
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#64748b;">
        {{ __('laravel-auth-kit::auth.mail.reset_expires', ['minutes' => $expireMinutes]) }}
    </p>

    <p style="margin:16px 0 0;font-size:12px;line-height:1.6;color:#94a3b8;word-break:break-all;">
        {{ __('laravel-auth-kit::auth.mail.reset_copy_link') }}<br>
        <a href="{{ $resetUrl }}" style="color:#4f46e5;">{{ $resetUrl }}</a>
    </p>
@endsection
