@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Employee</h1>

        <form action="{{ route('admin.employees.store') }}" method="POST">
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name: </label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="title">Title: </label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Title" value="{{ old('title') }}">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone: </label>
                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="color">Color: </label>
                        <input type="text" class="form-control" name="color" id="color" placeholder="Color" value="{{ old('color') }}">
                    </div>

                    <div class="form-group">
                        <label for="bgcolor">Background Color: </label>
                        <input type="text" class="form-control" name="bgcolor" id="bgcolor" placeholder="Background Color" value="{{ old('bgcolor') }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Employee</button>
            </div>
        </form>
    </div>
@endsection