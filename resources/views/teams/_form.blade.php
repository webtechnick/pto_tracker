<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name: <small>Display Name of Team</small></label>
            {{ html()->text('name')->class('form-control')->placeholder('Name')->required() }}
        </div>

    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="slug">Slug: <small>Slug of team, used in url</small></label>
            {{ html()->text('slug')->class('form-control')->placeholder('Slug')->required() }}
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="bgcolor">Description: <small>(optional)</small></label>
            {{ html()->textarea('description')->class('form-control')->placeholder('Description of Team')->attributes(['rows' => 5, 'cols' => 50]) }}
        </div>
    </div>
</div>