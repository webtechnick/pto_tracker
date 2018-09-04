@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.employees.delete', [$employee]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update Employee</h1>

        {{ Form::model($employee, ['route' => ['admin.employees.update', $employee], 'method' => 'POST']) }}
            @include('employees/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Employee</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection