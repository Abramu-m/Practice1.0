@extends('layouts.app_main_layout')

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Reset Password for {{ $user->first_name }} {{ $user->last_name }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('users.reset-password.post', $user->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Note (optional)</label>
                            <textarea id="note" name="note" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="notify_user" name="notify_user">
                            <label class="form-check-label" for="notify_user">Notify user about password change</label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-danger">Set Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
