@extends('layouts.error')

@section('code', '429')
@section('icon', '🚦')
@section('title', 'Too Many Requests')
@section('message')
    You've made too many requests in a short period. Please wait a moment and try again.
@endsection
