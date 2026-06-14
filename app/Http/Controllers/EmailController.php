<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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
