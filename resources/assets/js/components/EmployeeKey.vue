<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>All Resource Units</strong>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item" v-for="employee in employees">
                        <h5 v-html="render(employee)"></h5>
                        <span v-html="daysLeft(employee)"></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'employeekey',
    props: {
        employees: {
            default () {
                return []
            }
        }
    },
    methods: {
        render(employee) {
            return `<a href="?q=${employee.name}"><span class="employee" style="background-color:${employee.bgcolor}; color: ${employee.color};">${employee.name}</span></a>`;
        },
        daysLeft(employee) {
            let retval = `<span class="pto-left"><strong>${employee.days_left}</strong> PTO day(s) left.`;
            if (employee.days_left != employee.pending_days_left) {
                retval += ` <small>(Pending Left: ${employee.pending_days_left})</small>`;
            }
            retval += '</span>';
            return retval;
        }
    }
}
</script>
<style>
.employees li {
    /*list-style: none;*/
    border-bottom: 1px solid black;
}
.employee {
    border-radius: 5px;
    padding: 3px;
}
</style>