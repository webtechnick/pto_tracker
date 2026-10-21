@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.teams.delete', [$team]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update Team</h1>

        {{ html()->modelForm($team, 'POST', route('admin.teams.update', $team))->open() }}
            @include('teams/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Team</button>
            </div>
        {{ html()->closeModelForm() }}
    </div>
@endsection