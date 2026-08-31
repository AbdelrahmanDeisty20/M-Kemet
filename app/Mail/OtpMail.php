<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $code;
    public ?string $userName;

    public function __construct(string $code, ?string $userName = null)
    {
        $this->code     = $code;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.otp_mail_subject'),
        );
    }

    public function content(): Content
    {
        $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
        $align = app()->getLocale() === 'ar' ? 'right' : 'left';
        $welcome = !empty($this->userName) 
            ? __('messages.otp_mail_welcome', ['name' => $this->userName]) 
            : __('messages.otp_mail_welcome_generic');
        $intro = __('messages.otp_mail_intro');
        $expiry = __('messages.otp_mail_expiry');
        $ignore = __('messages.otp_mail_ignore');

        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; direction: {$dir}; text-align: {$align}; padding: 20px; color: #333;'>
                    <h2>{$welcome}</h2>
                    <p>{$intro}</p>
                    <div style='font-size: 24px; font-weight: bold; letter-spacing: 4px; color: #1d4ed8; background: #f3f4f6; padding: 12px 24px; display: inline-block; border-radius: 8px; margin: 16px 0;'>
                        {$this->code}
                    </div>
                    <p><strong>{$expiry}</strong></p>
                    <p>{$ignore}</p>
                </div>
            "
        );
    }
}
