<ul>
    @foreach ($ptos as $pto)
        <li>{{ $pto->employee->name }}</li>
    @endforeach
</ul>