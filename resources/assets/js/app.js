/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Form from './form.js';
//import Employee from './employee.js';

//Vue.component('example', require('./components/Example.vue'));
Vue.component('calendar', require('./components/Calendar.vue'));
Vue.component('currentday', require('./components/CurrentDay.vue'));
Vue.component('employeekey', require('./components/EmployeeKey.vue'));

const app = new Vue({
    el: '#app',

    data: {
        ptos: [],
        holidays: [],
        employees: [],
        form: new Form({
            employee_id: '',
            start_time: '',
            end_time: '',
            description: ''
        }),
        year: $('#inputyear').val(),
        admin: false
    },

    mounted() {
        this.getPtos();
        this.getHolidays();
        this.getEmployees();
        this.isAdmin();
    },

    methods: {
        onSubmit() {
            this.form.start_time = $( "#start_time" ).datepicker( "getDate" );
            this.form.end_time = $( "#end_time" ).datepicker( "getDate" );
            this.form.submit();
        },
        isAdmin() {
            axios.get('/is_admin')
                .then(response => this.admin = response.data)
                .catch(function(error) {
                    console.log(error);
                });
        },
        getEmployees() {
            axios.get('/get/employees/')
                 .then(response => this.employees = response.data)
                 .catch(function(error) {
                    console.log(error);
                 });
        },
        getHolidays() {
            axios.get('/get/holidays/' + this.year)
                 .then(response => this.holidays = response.data)
                 .catch(function(error) {
                    console.log(error);
                 });
        },
        getPtos() {
            axios.get('/get/ptos/' + this.year)
                 .then(response => this.ptos = response.data)
                 .catch(function(error) {
                    console.log(error);
                 });
        }
    }
});
