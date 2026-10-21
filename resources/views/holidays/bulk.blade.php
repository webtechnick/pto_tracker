@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Bulk Holiday Add</h1>

        {{ html()->form('POST', route('admin.holidays.bulkstore'))->open() }}
            <div class="form-group">
                <label for="body">CSV Input: </label> <small>Format: Holiday Title, YYYY-MM-DD, 1/0 (optional: Half Day True/False)</small>
                {{ html()->textarea('bulk')->class('form-control')->attributes(['rows' => 10, 'cols' => 50])->placeholder('Holiday 1, 2022-01-02
Holiday 2, 2022-07-04
Holiday Half, 2022-08-14, 1
Holiday 4, 2022-12-25
Holiday 5, 2022-12-26') }}
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Bulk Save</button>
            </div>
        {{ html()->form()->close() }}


        <h1>Add Summer Hours</h1>

        {{ html()->form('POST', route('admin.holidays.halfstore'))->open() }}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="body">Start: </label>
                    {{ html()->text('start')->class('form-control datepicker')->id('start')->placeholder('Start Date')->attribute('autocomplete', 'off')->required() }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="body">End: </label>
                    {{ html()->text('end')->class('form-control datepicker')->id('end')->placeholder('End Date')->attribute('autocomplete', 'off')->required() }}
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Half Hours</button>
        </div>

        {{ html()->form()->close() }}

    </div>
@endsection

@section('scripts')
<script>
$( function() {
    $("#start").datepicker({
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        onSelect: function(date) {
            $("#end").datepicker("option", "minDate", date);
        }
    });
    $("#end").datepicker({
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        onSelect: function(date) {
            $("#start").datepicker("option", "maxDate", date);
        }
    });
});
</script>
@endsection