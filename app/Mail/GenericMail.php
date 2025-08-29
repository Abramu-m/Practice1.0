<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $bodyHtml;
    public $attachments = [];

    /**
     * Create a new message instance.
     */
    /**
     * @param string $subjectLine
     * @param string $bodyHtml
     * @param array $attachments Array of file paths to attach
     */
    public function __construct(string $subjectLine, string $bodyHtml, array $attachments = [])
    {
        $this->subjectLine = $subjectLine;
        $this->bodyHtml = $bodyHtml;
        $this->attachments = $attachments;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $m = $this->subject($this->subjectLine)
                  ->view('emails.generic')
                  ->with(['bodyHtml' => $this->bodyHtml]);

        // Attach files if provided and readable
        foreach ($this->attachments as $file) {
            if ($file && is_string($file) && file_exists($file) && is_readable($file)) {
                $m->attach($file);
            }
        }

        return $m;
    }
}
