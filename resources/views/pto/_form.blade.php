<form action="/ptos/store" method="POST">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="employee_id">Resource Unit:</label>
        <select name="employee_id" id="employee" class="form-control" required>
            <option value="">Select One..</option>
            @foreach ( $employees as $employee )
                <option value="{{ $employee->id }}" @if(isset($user) && $user->name == $employee->name) selected @endif>{{ $employee->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="start">Start: </label>
        <input type="text" class="form-control datepicker" name="start_time" id="start_time" placeholder="Start Date" value="{{ old('start') }}" autocomplete="off" required>
    </div>
    <div class="form-group">
        <label for="end">End: </label>
        <input type="text" class="form-control datepicker" name="end_time" id="end_time" placeholder="End Date" value="{{ old('end') }}" autocomplete="off" required>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="half_day" id="half_day" value="1"> Half Day?<br><small>Specify morning or afternoon below</small>
        </label>
    </div>
    <div class="form-group">
        <label for="description">Description (optional): </label>
        <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
    </div>

    @include('layouts/_errors')
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Request PTO</button>
    </div>
</form>

@section('scripts')
<script>
$( function() {
    $("#start_time").datepicker({
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        onSelect: function(date) {
            $("#end_time").datepicker("option", "minDate", date);
        }
    });
    $("#end_time").datepicker({
        dateFormat: 'yy-mm-dd',
        gotoCurrent: true,
        onSelect: function(date) {
            $("#start_time").datepicker("option", "maxDate", date);
        }
    });
});
</script>
@endsection