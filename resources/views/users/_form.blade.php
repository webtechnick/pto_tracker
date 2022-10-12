<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name: </label>
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Name', 'required']) }}
        </div>

        <div class="form-group">
            <label for="role">Role: </label>
            {{ Form::select('role', App\User::$roles, null, ['class' => 'form-control', 'placeholder' => 'Select One..', 'required']) }}
            <span class="help-text">Admin: <small>Manage all PTO, Users, Employees, Holidays, and Teams</small></span><br>
            <span class="help-text">Manager: <small>Manage direct report PTO, and All Employees</small></span><br>
            <span class="help-text">Planner: <small>Can see PTO days remaining</small></span><br>
            <span class="help-text">User: <small>No permissions (Default)</small></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email: </label>
            {{ Form::text('email', null, ['class' => 'form-control', 'type' => 'email', 'placeholder' => 'first.last@continued.com', 'required']) }}
        </div>
    </div>
</div>