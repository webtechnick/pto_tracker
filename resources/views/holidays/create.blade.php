@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Holiday</h1>

        {{ Form::open(['route' => 'admin.holidays.store']) }}
            @include('holidays/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Holiday</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection