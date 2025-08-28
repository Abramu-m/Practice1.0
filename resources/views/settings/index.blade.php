@extends('layouts.app_main_layout')

@section('page_title', 'Settings')

@section('main_content')
<div class="container">
  <h4>Settings</h4>
  <p class="text-muted">Manage application-wide settings here. This page is a placeholder — implement settings UI as needed.</p>

  <div class="card">
    <div class="card-body">
      <p>No configurable settings yet.</p>
      <!-- Example: link to profile settings -->
      <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile Settings</a>
    </div>
  </div>
</div>
@endsection
