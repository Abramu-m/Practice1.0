@extends('layouts.error')

@section('code', $exception->getStatusCode())
@section('icon', '⚠️')
@section('title', 'Server Error')
@section('message')
    An unexpected server error occurred. The issue has been logged. Please try again, and contact support if the problem continues.
@endsection
