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

//Vue.component('example', require('./components/Example.vue'));
Vue.component('calendar', require('./components/Calendar.vue'));
Vue.component('currentday', require('./components/CurrentDay.vue'));

const app = new Vue({
    el: '#app',

    data: {
        selectedDay: {},
        ptos: [],
        holidays: [],
        employees: [],
        form: new Form({
            employee_id: '',
            start_time: '',
            end_time: '',
            description: ''
        }),
    },

    mounted() {
        this.getPtos(moment().format('YYYY'));
        this.getHolidays(moment().format('YYYY'));
    },

    methods: {
        onSubmit() {
            this.form.start_time = $( "#start_time" ).datepicker( "getDate" );
            this.form.end_time = $( "#end_time" ).datepicker( "getDate" );
            this.form.submit();
        },
        getHolidays(year) {
            axios.get('/get/holidays/' + moment().format('YYYY'))
                 .then(response => this.holidays = response.data)
                 .catch(function(error) {
                    console.log(errror);
                 });
        },
        getPtos(year) {
            axios.get('/get/ptos/' + moment().format('YYYY'))
                 .then(response => this.ptos = response.data)
                 .catch(function(error) {
                    console.log(errror);
                 });
        }
    }
});
