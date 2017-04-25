@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Update Employee</h1>

        <form action="{{ route('admin.employees.update', [$employee]) }}" method="POST">
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name: </label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="{{ $employee->name }}">
                    </div>

                    <div class="form-group">
                        <label for="title">Title: </label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Title" value="{{ $employee->title }}">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone: </label>
                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone" value="{{ $employee->phone }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="color">Color: </label>
                        <input type="text" class="form-control" name="color" id="color" placeholder="Color" value="{{ $employee->color }}">
                    </div>

                    <div class="form-group">
                        <label for="bgcolor">Background Color: </label>
                        <input type="text" class="form-control" name="bgcolor" id="bgcolor" placeholder="Background Color" value="{{ $employee->bgcolor }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Employee</button>
            </div>
        </form>
    </div>
@endsection