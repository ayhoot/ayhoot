<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class EmailSend extends Mailable
{
    use Queueable, SerializesModels;

    // REMOVED: public string $subject;
    // The $subject property is inherited from the parent Mailable class.
    // We do not need to (and cannot) re-declare it with a type hint.

    /**
     * Your custom content for the email body. This is fine to declare.
     * @var string
     */
    public string $dynamicContent;

    /**
     * Create a new message instance.
     *
     * @param string $subject The email subject.
     * @param string $dynamicContent The main content for the email body.
     */
    public function __construct(string $subject, string $dynamicContent)
    {
        // ADDED: This line sets the subject property inherited from the parent Mailable.
        $this->subject = $subject;
        
        $this->dynamicContent = $dynamicContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // This is correct. It will now use the $subject property we set in the constructor.
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $rtlLanguages = ['fa', 'ar', 'he'];
        $currentLocale = App::getLocale();
        $direction = in_array($currentLocale, $rtlLanguages) ? 'rtl' : 'ltr';
        $textAlign = $direction == 'rtl' ? 'right' : 'left';

        return new Content(
            view: 'email.template',
            with: [
                'dynamicContent' => $this->dynamicContent,
                'direction' => $direction,
                'textAlign' => $textAlign,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}