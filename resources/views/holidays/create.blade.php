@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Holiday</h1>

        {{ html()->form('POST', route('admin.holidays.store'))->open() }}
            @include('holidays/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Holiday</button>
            </div>
        {{ html()->form()->close() }}
    </div>
@endsection