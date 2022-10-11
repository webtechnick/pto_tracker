@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New User</h1>

        {{ Form::open(['route' => 'admin.users.store']) }}
            @include('users/_form')
            <h3>Password</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password: </label>
                        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => 'password', 'required']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password: </label>
                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'password', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection