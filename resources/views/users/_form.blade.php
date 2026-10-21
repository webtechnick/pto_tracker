<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name: </label>
            {{ html()->text('name')->class('form-control')->placeholder('Name')->required() }}
        </div>

        <div class="form-group">
            <label for="role">Role: </label>
            {{ html()->select('role', App\User::$roles)->class('form-control')->placeholder('Select One..')->required() }}
            <span class="help-text">Admin: <small>Manage all PTO, Users, Employees, Holidays, and Teams</small></span><br>
            <span class="help-text">Manager: <small>Manage direct report PTO, and all employees</small></span><br>
            <span class="help-text">Planner: <small>Can see PTO days remaining of fellow team members</small></span><br>
            <span class="help-text">User: <small>Employee account. No permissions. (default)</small></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email: </label>
            {{ html()->text('email')->class('form-control')->placeholder('first.last@continued.com')->required() }}
        </div>

        <div class="form-group">
            <label for="employee">Employee: (optional) </label>
            {{ html()->select('employee_id', App\Employee::orderBy('name', 'ASC')->pluck('name','id'))->class('form-control')->placeholder('Select one..') }}
            <span class="help-text">Suggested for 'Planner' role: <small>User can only see PTO of other team members of Employee specified</small></span><br>
        </div>
    </div>
</div>