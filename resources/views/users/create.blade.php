@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New User</h1>

        {{ html()->form('POST', route('admin.users.store'))->open() }}
            @include('users/_form')
            <h3>Password</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password: </label>
                        {{ html()->password('password')->class('form-control')->placeholder('password')->required() }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password: </label>
                        {{ html()->password('password_confirmation')->class('form-control')->placeholder('password')->required() }}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        {{ html()->form()->close() }}
    </div>
@endsection