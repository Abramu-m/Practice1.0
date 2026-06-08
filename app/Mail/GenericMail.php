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
    public $filePaths = [];

    /**
     * Create a new message instance.
     */
    /**
     * @param string $subjectLine
     * @param string $bodyHtml
     * @param array $filePaths Array of file paths to attach
     */
    public function __construct(string $subjectLine, string $bodyHtml, array $filePaths = [])
    {
        $this->subjectLine = $subjectLine;
        $this->bodyHtml = $bodyHtml;
        $this->filePaths = $filePaths;
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
        foreach ($this->filePaths as $file) {
            if ($file && is_string($file) && file_exists($file) && is_readable($file)) {
                $m->attach($file);
            }
        }

        return $m;
    }
}
