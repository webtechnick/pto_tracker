@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('manager.employees.delete', [$employee]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update Employee</h1>

        {{ html()->modelForm($employee, 'POST', route('manager.employees.update', $employee))->open() }}
            @include('employees/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Employee</button>
            </div>
        {{ html()->closeModelForm() }}
    </div>
@endsection