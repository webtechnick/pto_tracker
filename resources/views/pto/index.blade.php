@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-9">
            <div class="calendar">
                <ul class="january">
                    <li v-for="pto in ptos">
                        <span class="name" v-text="pto.employee.name"></span>
                        <span class="date" v-text="dateTimeText(pto)"></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-3">
            @include('pto._form')
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            VIEW DAY PTO
        </div>
        <div class="col-md-3">
            PTO LEFT PER EMPLOEE
        </div>
    </div>
</div>
@endsection