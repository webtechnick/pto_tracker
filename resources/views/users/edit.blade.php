@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.users.delete', [$user]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update User</h1>

        {{ Form::model($user, ['route' => ['admin.users.update', $user], 'method' => 'POST']) }}
            @include('users/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection