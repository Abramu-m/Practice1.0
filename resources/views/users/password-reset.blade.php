@extends('layouts.app_main_layout')

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Send Password Reset Link</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.password.send-reset') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Select User</label>
                            <select id="email" name="email" class="form-select" required>
                                <option value="">-- Choose user by email --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->email }}">{{ $user->first_name }} {{ $user->last_name }} &lt;{{ $user->email }}&gt;</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Note (optional)</label>
                            <textarea id="note" name="note" class="form-control" rows="3" placeholder="Reason for reset (e.g. user requested via phone)"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="cc_admins" name="cc_admins">
                            <label class="form-check-label" for="cc_admins">
                                Notify other admins about this action
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" id="send-btn" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('send-btn').addEventListener('click', function() {
    const email = document.getElementById('email').value;
    if (!email) {
        alert('Please choose a user.');
        return;
    }

    const note = document.getElementById('note').value;
    let confirmMsg = `Send password reset link to ${email}?`;
    if (note) confirmMsg += `\n\nNote: ${note}`;

    if (confirm(confirmMsg)) {
        // submit the form
        this.closest('form').submit();
    }
});
</script>
@endsection
