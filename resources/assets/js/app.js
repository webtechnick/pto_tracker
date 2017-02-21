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

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app',

    data: {
        selectedDay: {},
        ptos: {},
        form: new Form({
            employee_id: '',
            start_time: '',
            end_time: '',
            description: ''
        }),
    },

    mounted() {
        this.getPtos(moment().format('YYYY'));
    },

    methods: {
        onSubmit() {
            this.form.start_time = $( "#start_time" ).datepicker( "getDate" );
            this.form.end_time = $( "#end_time" ).datepicker( "getDate" );
            this.form.submit();
        },

        daySelect(day) {
            this.selectedDay = day; //a moment object?
        },

        dateTimeText(pto) {
            console.log(pto);
            let start = moment(pto.start_time).format('l');
            let end = moment(pto.end_end).format('l');
            return start + ' to ' + end;
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
