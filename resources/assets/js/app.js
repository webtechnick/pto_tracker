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
import Vue from 'vue';
import Calendar from './components/Calendar.vue';
import CurrentDay from './components/CurrentDay.vue';
import EmployeeKey from './components/EmployeeKey.vue';
import BounceLoader from 'vue-spinner/src/BounceLoader.vue';

Vue.component('calendar', Calendar);
Vue.component('currentday', CurrentDay);
Vue.component('employeekey', EmployeeKey);
Vue.component('bounce-loader', BounceLoader);

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
        team: $('#inputteam').val(),
        admin: false
    },

    mounted() {
        if (this.year) {
            Events.$on('reloadData', this.loadData.bind(this));
            this.getHolidays();
            this.loadData();
            this.isAdmin();
        }
    },

    methods: {
        loadData() {
            Events.$emit('loading');
            this.getPtos();
        },
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
            var url = '/get/ptos/' + this.year;
            if (this.team) {
                url = '/get/ptos/' + this.team + '/' + this.year;
            }
            console.log(url);
            console.log(this.team);
            axios.get(url)
                 .then(function(response) {
                    this.ptos = response.data;
                    Events.$emit('finishedLoading');
                 }.bind(this))
                 .catch(function(error) {
                    console.log(error);
                 });
        }
    }
});
