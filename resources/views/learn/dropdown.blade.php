@extends('layouts.app_main_layout')

@section('page_title', 'Searchable Dropdown')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Example: Searchable Dropdown using Select2</h1>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        Searchable User Dropdown Example
                    </h3>
                </div>
                <div class="card-body">
                        <select class="form-control" id="namesSelect" name="names_select" aria-label="Searchable names select"></select>
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
$(document).ready(function () {
    $('#namesSelect').select2({
        ajax: {
            url: '{{ route("learn.user_search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { name: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.results,
                };
            }
        },
        minimumInputLength: 2,
        placeholder: 'Search users...',
        allowClear: true,
        width: '100%',
        templateResult: formatResult,
        templateSelection: formatSelection,
    });

        function formatResult(item) {
        if (item.loading) return item.text;
        var id = item.id;
        var name = item.name || item.text;
        var email = item.email;
        var dob = item.date_of_birth;
        var html = '<div>' +
            '<p> Fullname:' + name + '</p>' +
            '<p> Email: ' + email+ '</p>' +
            '<p> Date of Birth: ' + dob + '</p>'+
            '<p> ID: ' + id + '</p>'+
            '</div>';
        return $(html);
        }

      function formatSelection(item) {
        //return name and email
        
      }

        $('#namesSelect').on('select2:open', function () {
            setTimeout(function () {
                document.querySelector('.select2-container--open .select2-search__field').focus();
            }, 0);
        });
});
</script>
@endsection
