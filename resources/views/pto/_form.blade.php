<div class="panel panel-default pto-request-panel">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-calendar"></span>
        <strong>Request Time Off</strong>
    </div>
    <div class="panel-body">
        <form action="/ptos/store" method="POST">
            {{ csrf_field() }}
            
            <div class="form-group">
                <label for="employee_id">
                    <span class="glyphicon glyphicon-user"></span> Who's taking PTO?
                </label>
                <select name="employee_id" id="employee" class="form-control" required>
                    <option value="">Select Employee...</option>
                    @foreach ( $employees as $employee )
                        <option value="{{ $employee->id }}" @if(isset($user) && $user->name == $employee->name) selected @endif>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="date-range-group">
                <div class="form-group date-field">
                    <label for="start">
                        <span class="glyphicon glyphicon-log-out"></span> Start
                    </label>
                    <input type="text" class="form-control datepicker" name="start_time" id="start_time" placeholder="YYYY-MM-DD" value="{{ old('start') }}" autocomplete="off" required>
                </div>
                <div class="date-arrow">
                    <span class="glyphicon glyphicon-arrow-right"></span>
                </div>
                <div class="form-group date-field">
                    <label for="end">
                        <span class="glyphicon glyphicon-log-in"></span> End
                    </label>
                    <input type="text" class="form-control datepicker" name="end_time" id="end_time" placeholder="YYYY-MM-DD" value="{{ old('end') }}" autocomplete="off" required>
                </div>
            </div>

            <div class="checkbox half-day-toggle">
                <label>
                    <input type="checkbox" name="half_day" id="half_day" value="1">
                    <span class="glyphicon glyphicon-adjust"></span> Half day only
                </label>
                <small class="help-text">Specify AM or PM in Public Note</small>
            </div>

            <div class="form-group">
                <label for="description">
                    <span class="glyphicon glyphicon-comment"></span> Public Note <span class="text-muted">(optional)</span>
                </label>
                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Vacation, Doctor's Appointment, Wedding, Birthday, etc...">{{ old('description') }}</textarea>
            </div>

            @include('layouts/_errors')
            
            <button type="submit" class="btn btn-block pto-submit-btn">
                <span class="glyphicon glyphicon-send"></span> Submit Request
            </button>
        </form>
    </div>
</div>

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