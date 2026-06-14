@extends('layouts.app_main_layout')

@section('page_title', 'Email')

@php
    $titles = ['reply' => 'Reply', 'forward' => 'Forward Message', null => 'New Message'];
    $title = $titles[$prefill['mode']] ?? 'New Message';
@endphp

@section('main_content')
<div class="container-fluid">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="bi bi-envelope-plus me-2"></i>{{ $title }}</h4>
        <a href="{{ route('email.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Inbox
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('email.send') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <input type="hidden" name="mode" value="{{ $prefill['mode'] }}">
                <input type="hidden" name="original_uid" value="{{ $prefill['original_uid'] }}">
                <input type="hidden" name="original_folder" value="{{ $prefill['original_folder'] }}">
                <input type="hidden" name="in_reply_to" value="{{ $prefill['in_reply_to'] }}">

                <div class="col-12">
                    <label class="form-label fw-semibold">To</label>
                    <input type="text" name="to" class="form-control @error('to') is-invalid @enderror" value="{{ old('to', $prefill['to']) }}" placeholder="name@example.com, another@example.com" required>
                    @error('to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cc</label>
                    <input type="text" name="cc" class="form-control @error('cc') is-invalid @enderror" value="{{ old('cc', $prefill['cc']) }}">
                    @error('cc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bcc</label>
                    <input type="text" name="bcc" class="form-control @error('bcc') is-invalid @enderror" value="{{ old('bcc') }}">
                    @error('bcc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Subject</label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $prefill['subject']) }}">
                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea name="body" rows="12" class="form-control @error('body') is-invalid @enderror">{{ old('body', $prefill['body']) }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if($prefill['mode'] === 'forward' && count($prefill['attachments']))
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-paperclip me-1"></i>
                            The original attachment(s) will be forwarded with this message:
                            {{ collect($prefill['attachments'])->pluck('name')->implode(', ') }}
                        </div>
                    </div>
                @endif

                <div class="col-12">
                    <label class="form-label fw-semibold">Attach Files</label>
                    <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" multiple>
                    @error('attachments.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send
                    </button>
                    <a href="{{ route('email.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
