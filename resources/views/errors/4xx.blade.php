@extends('layouts.error')

@section('code', $exception->getStatusCode())
@section('icon', '❓')
@section('title', 'Request Problem')
@section('message')
    There was a problem with your request. Please go back and try again, or contact an administrator if the problem continues.
@endsection
