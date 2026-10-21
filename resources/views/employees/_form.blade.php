<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name: </label>
            {{ html()->text('name')->class('form-control')->placeholder('Name') }}
        </div>

        <div class="form-group">
            <label for="title">Title: </label>
            {{ html()->text('title')->class('form-control')->placeholder('Title') }}
        </div>

        <div class="form-group">
            <label for="phone">Phone: </label>
            {{ html()->text('phone')->class('form-control')->placeholder('Phone') }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="color">Color: </label>
            {{ html()->text('color')->class('form-control')->placeholder('Color') }}
        </div>

        <div class="form-group">
            <label for="bgcolor">Background Color: </label>
            {{ html()->text('bgcolor')->class('form-control')->placeholder('Background Color') }}
        </div>

        <div class="form-group">
            <label for="max_days_off">Custom Max Days Off: <small>If left empty, the default {{ config('app.max_days_off') }} will be used.</small></label>
            {{ html()->text('max_days_off')->class('form-control')->placeholder('21') }}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tag_string">Teams: <small>Separate by comma, if team doesn't exist it will be created</small></label>
            {{ html()->text('tag_string')->class('form-control')->placeholder('Backend, Frontend, Billing, Simucase, etc...') }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="manager_id">Manager: <small>If manager is not in list, please ask manager to create a PTO account</small></label>
            {{ html()->select('manager_id', App\User::allManagers()->orderBy('name','ASC')->pluck('name','id'))->class('form-control')->placeholder('Select One..') }}
        </div>
        <div class="checkbox">
            <label>
                <input type="hidden" name="is_contractor" value="0">
                {{ html()->checkbox('is_contractor', null, 1) }}
                Contractor <small>(holidays count as work days)</small>
            </label>
        </div>
    </div>
</div>