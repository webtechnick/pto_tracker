<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Teams</strong>
    </div>
    <div class="panel-body">
        <div class="list-group">
            <a href="/" class="list-group-item @if(!$team) active @endif">All</a>
            @foreach($teams as $t)
                <a href="{{ $t->link() }}" class="list-group-item @if($t->slug == $team) active @endif">
                    {{ $t->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>