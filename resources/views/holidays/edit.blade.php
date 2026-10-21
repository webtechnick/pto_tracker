@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.holidays.delete', [$holiday]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update Holiday</h1>

        {{ html()->modelForm($holiday, 'POST', route('admin.holidays.update', $holiday))->open() }}
            @include('holidays/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Holiday</button>
            </div>
        {{ html()->closeModelForm() }}
    </div>
@endsection