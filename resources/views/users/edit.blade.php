@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.users.delete', [$user]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update User</h1>

        {{ html()->modelForm($user, 'POST', route('admin.users.update', $user))->open() }}
            @include('users/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        {{ html()->closeModelForm() }}
    </div>
@endsection