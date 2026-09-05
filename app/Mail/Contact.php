<?php

namespace App\Mail;

use App\Http\Requests\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class Contact extends Mailable
{
    use Queueable, SerializesModels;

    private mixed $params;

    /**
     * Create a new message instance.
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email liên hệ từ website',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contact',
            with: ['data' => $this->params]
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $message = $this->view('mail.contact')
            ->with(['data' => $this->params])
            ->subject('Email liên hệ từ website');

     /*   $image = request()->file('image');
        if ($image) {
            $path = Storage::disk('admin')->put('email', $image);
            $message->attach(Storage::disk('admin')->path($path));
        }*/

        return $message;
    }

}
