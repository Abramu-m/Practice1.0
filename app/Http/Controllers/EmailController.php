<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Webklex\PHPIMAP\Client;

class EmailController extends Controller
{
    /**
     * Show the inbox, or the mailbox connection form if the user hasn't
     * saved their mailbox credentials yet.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $facility = Facility::current();

        if (!$user->hasMailboxConnected()) {
            return view('email.connect');
        }

        if (!$facility->imap_host) {
            return view('email.connect')->with('error', 'The facility mail server has not been configured yet. Contact your administrator.');
        }

        $folderPath = $request->query('folder', 'INBOX');

        try {
            $client = $this->connectMailbox($user, $facility);

            $folders = $client->getFolders(false)->reject(fn ($f) => $f->no_select);
            $folder = $client->getFolder($folderPath) ?? $client->getFolder('INBOX');

            $messages = $folder->messages()
                ->whereAll()
                ->setFetchOrder('desc')
                ->paginate(25);

            $client->disconnect();
        } catch (\Throwable $e) {
            Log::warning('IMAP connection failed for user ' . $user->id . ': ' . $e->getMessage());

            return view('email.connect')->with('error', 'Could not connect to your mailbox: ' . $e->getMessage());
        }

        return view('email.index', [
            'messages' => $messages,
            'folders' => $folders,
            'folderPath' => $folder->path,
        ]);
    }

    /**
     * Save the user's mailbox password after verifying it works.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'imap_password' => 'required|string',
        ]);

        $user = $request->user();
        $facility = Facility::current();

        if (!$facility->imap_host) {
            return back()->with('error', 'The facility mail server has not been configured yet. Contact your administrator.');
        }

        try {
            $client = $facility->makeImapClient($user->email, $request->input('imap_password'));
            $client->connect();
            $client->disconnect();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not connect to your mailbox: ' . $e->getMessage());
        }

        $user->imap_password = $request->input('imap_password');
        $user->save();

        return redirect()->route('email.index')->with('success', 'Mailbox connected successfully.');
    }

    /**
     * Forget the user's saved mailbox password.
     */
    public function disconnect(Request $request)
    {
        $user = $request->user();
        $user->imap_password = null;
        $user->save();

        return redirect()->route('email.index')->with('success', 'Mailbox disconnected.');
    }

    /**
     * View a single message.
     */
    public function show(Request $request, int $uid)
    {
        $user = $request->user();
        $facility = Facility::current();

        if (!$user->hasMailboxConnected() || !$facility->imap_host) {
            return redirect()->route('email.index');
        }

        $folderPath = $request->query('folder', 'INBOX');

        try {
            $client = $this->connectMailbox($user, $facility);
            $folder = $client->getFolder($folderPath) ?? $client->getFolder('INBOX');
            $message = $folder->messages()->getMessageByUid($uid);
            $client->disconnect();
        } catch (\Throwable $e) {
            Log::warning('IMAP fetch failed for user ' . $user->id . ': ' . $e->getMessage());

            return view('email.connect')->with('error', 'Could not load this message: ' . $e->getMessage());
        }

        return view('email.show', [
            'message' => $message,
            'folderPath' => $folder->path,
        ]);
    }

    /**
     * Download a single message attachment.
     */
    public function attachment(Request $request, int $uid)
    {
        $user = $request->user();
        $facility = Facility::current();

        $folderPath = $request->query('folder', 'INBOX');
        $partNumber = $request->query('part');

        try {
            $client = $this->connectMailbox($user, $facility);
            $folder = $client->getFolder($folderPath) ?? $client->getFolder('INBOX');
            $message = $folder->messages()->getMessageByUid($uid);

            $attachment = $message->attachments()->first(fn ($a) => (string) $a->part_number === (string) $partNumber);

            $client->disconnect();
        } catch (\Throwable $e) {
            abort(404, 'Attachment not found: ' . $e->getMessage());
        }

        if (!$attachment) {
            abort(404, 'Attachment not found.');
        }

        return new Response($attachment->content, 200, [
            'Content-Type' => $attachment->content_type ?: 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . ($attachment->name ?: 'attachment') . '"',
        ]);
    }

    /**
     * Show the compose form, optionally prefilled for a reply or forward.
     */
    public function compose(Request $request)
    {
        $user = $request->user();
        $facility = Facility::current();

        if (!$user->hasMailboxConnected() || !$facility->imap_host) {
            return redirect()->route('email.index');
        }

        $mode = $request->query('mode');

        $prefill = [
            'to' => '',
            'cc' => '',
            'subject' => '',
            'body' => '',
            'mode' => null,
            'original_uid' => null,
            'original_folder' => null,
            'in_reply_to' => null,
            'attachments' => [],
        ];

        if (in_array($mode, ['reply', 'forward']) && $request->query('uid')) {
            $uid = (int) $request->query('uid');
            $folderPath = $request->query('folder', 'INBOX');

            try {
                $client = $this->connectMailbox($user, $facility);
                $folder = $client->getFolder($folderPath) ?? $client->getFolder('INBOX');
                $original = $folder->messages()->getMessageByUid($uid);
                $client->disconnect();
            } catch (\Throwable $e) {
                Log::warning('IMAP fetch failed for user ' . $user->id . ': ' . $e->getMessage());

                return redirect()->route('email.index')->with('error', 'Could not load the original message: ' . $e->getMessage());
            }

            $originalSubject = mb_decode_mimeheader((string) $original->subject);
            $originalFrom = mb_decode_mimeheader((string) $original->from->first());
            $originalDate = $original->date->toDate()->format('M j, Y g:i A');
            $originalBody = $original->hasHTMLBody()
                ? trim(strip_tags($original->getHTMLBody()))
                : trim((string) $original->getTextBody());

            $prefill['mode'] = $mode;
            $prefill['original_uid'] = $uid;
            $prefill['original_folder'] = $folder->path;

            if ($mode === 'reply') {
                $fromAddress = $original->from->first();

                $messageId = (string) $original->message_id->first();
                if ($messageId !== '' && !str_starts_with($messageId, '<')) {
                    $messageId = '<' . $messageId . '>';
                }

                $prefill['to'] = $fromAddress?->mail ?? '';
                $prefill['subject'] = preg_match('/^Re:/i', $originalSubject) ? $originalSubject : 'Re: ' . $originalSubject;
                $prefill['in_reply_to'] = $messageId;
                $prefill['body'] = "\n\nOn {$originalDate}, {$originalFrom} wrote:\n" . preg_replace('/^/m', '> ', $originalBody);
            } else {
                $prefill['subject'] = preg_match('/^Fwd:/i', $originalSubject) ? $originalSubject : 'Fwd: ' . $originalSubject;
                $prefill['body'] = "\n\n---------- Forwarded message ----------\nFrom: {$originalFrom}\nDate: {$originalDate}\nSubject: {$originalSubject}\n\n{$originalBody}";

                if ($original->hasAttachments()) {
                    foreach ($original->attachments() as $attachment) {
                        $prefill['attachments'][] = [
                            'part_number' => $attachment->part_number,
                            'name' => $attachment->name,
                            'size' => $attachment->size,
                        ];
                    }
                }
            }
        }

        return view('email.compose', ['prefill' => $prefill]);
    }

    /**
     * Send a new message, reply, or forward.
     */
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
            'mode' => 'nullable|in:reply,forward',
            'original_uid' => 'nullable|integer',
            'original_folder' => 'nullable|string',
            'in_reply_to' => 'nullable|string',
            'forward_attachments' => 'nullable|array',
            'forward_attachments.*' => 'string',
        ]);

        $user = $request->user();
        $facility = Facility::current();

        if (!$user->hasMailboxConnected() || !$facility->imap_host) {
            return redirect()->route('email.index');
        }

        try {
            $email = (new MimeEmail())
                ->from(new Address($user->email, $user->name))
                ->subject((string) $request->input('subject', ''))
                ->text((string) $request->input('body', ''));

            foreach ($this->parseAddresses($request->input('to')) as $address) {
                $email->addTo($address);
            }

            foreach ($this->parseAddresses($request->input('cc')) as $address) {
                $email->addCc($address);
            }

            foreach ($this->parseAddresses($request->input('bcc')) as $address) {
                $email->addBcc($address);
            }

            if (empty($email->getTo())) {
                return back()->withInput()->with('error', 'Please enter at least one valid recipient address.');
            }

            foreach ($request->file('attachments', []) as $file) {
                if ($file && $file->isValid()) {
                    $email->attachFromPath($file->getRealPath(), $file->getClientOriginalName(), $file->getMimeType());
                }
            }

            if ($request->input('mode') === 'reply' && $request->filled('in_reply_to')) {
                $email->getHeaders()->addTextHeader('In-Reply-To', $request->input('in_reply_to'));
                $email->getHeaders()->addTextHeader('References', $request->input('in_reply_to'));
            }

            if ($request->input('mode') === 'forward' && $request->filled('original_uid')) {
                $selectedParts = $request->input('forward_attachments', []);

                $client = $this->connectMailbox($user, $facility);
                $folder = $client->getFolder($request->input('original_folder', 'INBOX')) ?? $client->getFolder('INBOX');
                $original = $folder->messages()->getMessageByUid((int) $request->input('original_uid'));

                foreach ($original->attachments() as $attachment) {
                    if (in_array((string) $attachment->part_number, $selectedParts, true)) {
                        $email->attach($attachment->content, $attachment->name, $attachment->content_type);
                    }
                }

                $client->disconnect();
            }

            $facility->makeMailer($user->email, $user->imap_password)->send($email);
        } catch (\Throwable $e) {
            Log::warning('Sending email failed for user ' . $user->id . ': ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not send the email: ' . $e->getMessage());
        }

        return redirect()->route('email.index')->with('success', 'Email sent successfully.');
    }

    /**
     * Parse a comma/semicolon separated list of "Name <email>" or "email" strings into Addresses.
     */
    protected function parseAddresses(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $addresses = [];

        foreach (preg_split('/[,;]/', $value) as $part) {
            $part = trim($part);

            if ($part !== '') {
                $addresses[] = Address::create($part);
            }
        }

        return $addresses;
    }

    /**
     * Build and connect an IMAP client for the given user using the
     * facility's mail server settings and the user's saved mailbox password.
     */
    protected function connectMailbox(User $user, Facility $facility): Client
    {
        $client = $facility->makeImapClient($user->email, $user->imap_password);
        $client->connect();

        return $client;
    }
}
