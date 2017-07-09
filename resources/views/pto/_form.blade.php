<!-- <form action="/ptos/store" method="POST" @submit.prevent="onSubmit"> -->
<form action="/ptos/store" method="POST">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="employee_id">Resource Unit:</label>
        <!-- <select v-model="form.employee_id" name="employee_id" id="employee" class="form-control"> -->
        <select name="employee_id" id="employee" class="form-control" required>
            <option value="">Select One..</option>
            @foreach ( $employees as $employee )
                <option value="{{ $employee->id }}" @if(isset($user) && $user->name == $employee->name) selected @endif>{{ $employee->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="start">Start: </label>
        <!-- <input v-model="form.start_time" type="text" class="form-control datepicker" name="start_time" id="start_time" placeholder="Start Date" value="{{ old('start') }}" required> -->
        <input type="text" class="form-control datepicker" name="start_time" id="start_time" placeholder="Start Date" value="{{ old('start') }}" required>
    </div>
    <div class="form-group">
        <label for="end">End: </label>
        <!-- <input v-model="form.end_time" type="text" class="form-control datepicker" name="end_time" id="end_time" placeholder="End Date" value="{{ old('end') }}" required> -->
        <input type="text" class="form-control datepicker" name="end_time" id="end_time" placeholder="End Date" value="{{ old('end') }}" required>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="half_day" id="half_day" value="1"> Half Day?<br><small>Specify morning or afternoon below</small>
        </label>
    </div>
    <div class="form-group">
        <label for="description">Description (optional): </label>
        <!-- <textarea v-model="form.description" class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea> -->
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
    $(".datepicker").datepicker({
        'dateFormat': 'yy-mm-dd'
    });
});
</script>
@endsection