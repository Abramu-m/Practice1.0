@extends('layouts.app_main_layout')

@section('page_title')
    {{'Create User' }}
 @endsection

@section('main_content')
    <div class="row">
        <div class="col-sm-12">
            @include('users.form.user_form', ['user' => null])
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('extra_footer_content')
@endsection
