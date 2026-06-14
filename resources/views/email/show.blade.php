@extends('layouts.app_main_layout')

@section('page_title', 'Email')

@php
    $subject = mb_decode_mimeheader((string) $message->subject);
@endphp

@section('main_content')
<div class="container-fluid">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope-open me-2"></i>{{ $subject ?: '(no subject)' }}</h4>
        <a href="{{ route('email.index', ['folder' => $folderPath]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Inbox
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-borderless table-sm w-auto mb-3">
                <tr>
                    <th class="text-muted">From</th>
                    <td>{{ mb_decode_mimeheader((string) $message->from->first()) }}</td>
                </tr>
                @if($message->to && $message->to->count())
                <tr>
                    <th class="text-muted">To</th>
                    <td>{{ mb_decode_mimeheader(implode(', ', array_map('strval', $message->to->all()))) }}</td>
                </tr>
                @endif
                @if($message->cc && $message->cc->count())
                <tr>
                    <th class="text-muted">Cc</th>
                    <td>{{ mb_decode_mimeheader(implode(', ', array_map('strval', $message->cc->all()))) }}</td>
                </tr>
                @endif
                <tr>
                    <th class="text-muted">Date</th>
                    <td>{{ $message->date->toDate()->format('M j, Y g:i A') }}</td>
                </tr>
            </table>

            @if($message->hasAttachments())
                <div class="mb-3">
                    <strong>Attachments:</strong>
                    @foreach($message->attachments() as $attachment)
                        <a href="{{ route('email.attachment', ['uid' => $message->uid, 'folder' => $folderPath, 'part' => $attachment->part_number]) }}"
                           class="btn btn-sm btn-outline-secondary me-1 mb-1">
                            <i class="bi bi-paperclip"></i> {{ $attachment->name }} ({{ round($attachment->size / 1024, 1) }} KB)
                        </a>
                    @endforeach
                </div>
            @endif

            <hr>

            @if($message->hasHTMLBody())
                <iframe class="w-100 border-0" style="min-height: 500px;" sandbox="" srcdoc="{{ $message->getHTMLBody() }}"></iframe>
            @else
                <div style="white-space: pre-wrap;">{{ $message->getTextBody() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
