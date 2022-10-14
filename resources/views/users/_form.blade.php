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
            <span class="help-text">Manager: <small>Manage direct report PTO, and all employees</small></span><br>
            <span class="help-text">Planner: <small>Can see PTO days remaining of fellow team members</small></span><br>
            <span class="help-text">User: <small>Employee account. No permissions. (default)</small></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email: </label>
            {{ Form::text('email', null, ['class' => 'form-control', 'type' => 'email', 'placeholder' => 'first.last@continued.com', 'required']) }}
        </div>

        <div class="form-group">
            <label for="employee">Employee: (optional) </label>
            {{ Form::select('employee_id', App\Employee::orderBy('name', 'ASC')->pluck('name','id'), null, ['class' => 'form-control', 'type' => 'email', 'placeholder' => 'Select one..']) }}
            <span class="help-text">Suggested for 'Planner' role: <small>User can only see PTO of other team members of Employee specified</small></span><br>
        </div>
    </div>
</div>