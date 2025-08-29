<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test {to} {--subject=Test email from app}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email using configured mailer (useful to verify Gmail SMTP)';

    public function handle()
    {
        $to = $this->argument('to');
        $subject = $this->option('subject');

        $body = "<p>This is a test email sent at " . now() . "</p>";

        try {
            Mail::to($to)->send(new GenericMail($subject, $body));
            $this->info("Email sent to {$to}. Check inbox/spam and provider logs.");
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
        }

        return 0;
    }
}
