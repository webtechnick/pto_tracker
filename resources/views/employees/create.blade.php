@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Employee</h1>

        {{ Form::open(['route' => 'manager.employees.store']) }}
            @include('employees/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Employee</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection