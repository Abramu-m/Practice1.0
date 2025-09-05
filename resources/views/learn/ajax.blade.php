@extends('layouts.app_main_layout')

@section('page_title', 'AJAX')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Example: Fetch User Data and display it as array in the box below</h1>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        AJAX Example
                        <button class="btn btn-sm btn-info float-right" onclick="fetchData()">Fetch Data</button>
                    </h3>
                </div>
                <div class="card-body">
                    <div id="ajaxContentBox" class="border p-3">
                        <!-- AJAX content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>

</style>
@endsection

@section('scripts')
<script>
    // Function to fetch data via jquery's AJAX
    function fetchData() {
        console.log('Fetching data...');
        $.ajax({
            url: '{{ route('learn.ajax.fetchData') }}',
            method: 'GET',
            data: {
                user: '{{ auth()->user()->id }}'
            },
            success: function(data) {
               console.log('Process and display data');
               console.log(data);
               content = '<pre>' + JSON.stringify(data, null, 4) + '</pre>';
                $('#ajaxContentBox').html(content);
            },
            error: function() {
                $('#ajaxContentBox').html('<p class="text-danger">Failed to fetch data.</p>');
            }
        });
    }
</script>
@endsection
