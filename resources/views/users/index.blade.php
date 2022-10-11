@extends('layouts.app')
@section('content')
    <div class="container">
        @foreach($users as $user)
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="name">{{ $user->name }}</span>
                    <small>(Role: {{ $user->role }})</small>
                    <div class="pull-right">
                        <div class="btn-group" role="group" aria-label="...">
                            <a href="{{ route('admin.users.edit', [$user]) }}" class="btn btn-default">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
                    <a href="/admin/users/create" class="btn btn-primary btn-lg">Create New User</a>
                </div>
            </div>
        </div>
    </div>
@endsection