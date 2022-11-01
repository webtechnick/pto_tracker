@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Bulk Holiday Add</h1>

        {{ Form::open(['route' => 'admin.holidays.bulkstore']) }}
            <div class="form-group">
                <label for="body">CSV Input: </label> <small>Format: Holiday Title, YYYY-MM-DD, 1/0 (optional: Half Day True/False)</small>
                {{ Form::textarea('bulk', null, ['placeholder' => 'Holiday 1, 2022-01-02
Holiday 2, 2022-07-04
Holiday Half, 2022-08-14, 1
Holiday 4, 2022-12-25
Holiday 5, 2022-12-26','class' => 'form-control']) }}
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Bulk Save</button>
            </div>
        {{ Form::close() }}


        <h1>Add Summer Hours</h1>

        {{ Form::open(['route' => 'admin.holidays.halfstore']) }}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="body">Start: </label>
                    {{ Form::text('start', null, ['class' => 'form-control datepicker', 'id' => 'start', 'placeholder' => 'Start Date', 'autocomplete' => 'off', 'required']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="body">End: </label>
                    {{ Form::text('end', null, ['class' => 'form-control datepicker', 'id' => 'end', 'placeholder' => 'End Date', 'autocomplete' => 'off', 'required']) }}
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Half Hours</button>
        </div>

        {{ Form::close() }}

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