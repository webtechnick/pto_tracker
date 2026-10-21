@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Employee</h1>

        {{ html()->form('POST', route('manager.employees.store'))->open() }}
            @include('employees/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Employee</button>
            </div>
        {{ html()->form()->close() }}
    </div>
@endsection