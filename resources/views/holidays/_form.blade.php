<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="title">Title: </label>
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Title']) }}
        </div>
        <div class="checkbox">
            <label>
                {{ Form::hidden('is_half_day',0) }}
                {{ Form::checkbox('is_half_day') }} Half Day Holiday? <span class="glyphicon glyphicon-adjust"></span>
            </label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="date">Date: </label>
            {{-- <input type="text" class="form-control datepicker" name="date" id="date" placeholder="Date" value="{{ old('date') }}" autocomplete="off" required> --}}
            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date']) }}
        </div>
    </div>
</div>

@section('scripts')
<script>
$( function() {
    $(".datepicker").datepicker({
        'dateFormat': 'yy-mm-dd'
    });
});
</script>
@endsection