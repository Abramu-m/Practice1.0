@extends('layouts.error')

@section('code', '419')
@section('icon', '⏳')
@section('title', 'Session Expired')
@section('message')
    Your session has expired, likely because the page was open for too long. Please go back, refresh the page, and log in again.
@endsection
