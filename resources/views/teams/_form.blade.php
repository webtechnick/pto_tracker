<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name: <small>Display Name of Team</small></label>
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Name', 'required']) }}
        </div>

    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="slug">Slug: <small>Slug of team, used in url</small></label>
            {{ Form::text('slug', null, ['class' => 'form-control', 'placeholder' => 'Slug', 'required']) }}
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="bgcolor">Description: <small>(optional)</small></label>
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Description of Team', 'rows' => 5]) }}
        </div>
    </div>
</div>