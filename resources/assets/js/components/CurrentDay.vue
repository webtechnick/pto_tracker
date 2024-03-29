<template>
    <div>
        <div class="panel panel-default" v-if="events.length">
            <div class="panel-heading">
                Day: {{ currentday.format('l') }}
            </div>
            <div class="panel-body">
                <ol class="list-group">
                    <li class="list-group-item" v-for="event in events">
                        <div class="pull-right">
                            <span v-html="isApproved(event.approved)"></span>
                            <span v-html="isHalfDay(event)"></span>
                            <span v-html="isSentToCalendar(event)"></span>
                        </div>
                        <h4 v-html="event.title"></h4>
                        <p v-text="event.description"></p>
                        <div class="btn-group">
                            <button v-if="showButton(event)" class="btn btn-success btn-sm" @click="approve(event)">Approve</button>
                            <button v-if="showButton(event)" class="btn btn-warning btn-sm" @click="deny(event)">Deny</button>
                            <button v-if="showButton(event)" class="btn btn-danger btn-sm" @click="remove(event)"><span class="glyphicon glyphicon-trash"></span></button>
                            <button v-if="showButton(event)" class="btn btn-info btn-sm" @click="addToGoogle(event)"><span class="glyphicon glyphicon-calendar"></span></button>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'currentday',
    props: {
        year: {
            default() {
                return moment().format('YYYY');
            }
        },
        ptos: {
            default() {
                return [];
            }
        },
        holidays: {
            default () {
                return [];
            }
        },
        admin: {
            default() {
                return false;
            }
        }
    },
    data() {
        return {
            'events': [],
            'currentday': moment()
        };
    },
    mounted() {
        Events.$on('dayselect', this.rePopulateEvents.bind(this));
        //Events.$on('finishedLoading', this.populateEvents.bind(this));
    },
    methods: {
        showButton(event) {
            // If employee can be managed by signed in.
            if (event.pto && event.pto.employee && event.pto.employee.can_manage) {
                return true;
            }
            // if not admin
            if (!this.admin) {
                return false;
            }
            // if event is a holiday
            if (event.holiday) {
                return false;
            }
            return true;
        },
        remove(event) {
            axios.post('/ptos/destroy/' + event.pto.id)
                .then(response => this.reloadData())
                .catch(function(error) {
                    console.log(error);
                });
            // this.reloadPage();
        },
        deny(event) {
            event.approved = false;
            axios.post('/ptos/deny/' + event.pto.id)
                .then(response => this.reloadData())
                .catch(function(error) {
                    console.log(error);
                });
            console.log(event);
            // this.reloadData();
        },
        approve(event) {
            event.approved = true;
            axios.post('/ptos/approve/' + event.pto.id)
                .then(response => this.reloadData())
                .catch(function(error) {
                    console.log(error);
                });
            console.log(event);
            // this.reloadData();
        },
        addToGoogle(event) {
            this.sentToCalendar(event);
            let start = moment(event.pto.start_time).format('YYYYMMDD');
            let end = moment(event.pto.end_time).add(1, 'days').format('YYYYMMDD');
            let options = {
                'action': 'TEMPLATE',
                'text': event.pto.employee.name + ' OOO - PTO',
                'details': event.pto.description,
                'trp': 'true',
                'dates': start + '/' + end
            };
            window.open('http://www.google.com/calendar/hosted/continued.com/event?' + $.param(options));
            // window.open('http://www.google.com/calendar/hosted/alliedhealthmedia.com/event?' + $.param(options));
        },
        sentToCalendar(event) {
            event.pto.is_sent_to_calendar = true;
            axios.post('/ptos/sent_to_calendar/' + event.pto.id)
                .then()
                .catch(function(error) {
                    console.log(error);
                });
        },
        reloadData() {
            Events.$emit('reloadData');
        },
        reloadPage() {
            location.reload();
        },
        populateEvents() {
            this.rePopulateEvents(this.currentday.format('MM'), this.currentday.format('DD'));
        },
        rePopulateEvents(month, day) {
            this.currentday = moment(this.year + '-' + month + '-' + day, 'YYYY-MM-DD');
            this.reset();

            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (this.currentday.isSame(holiday.date, 'day')) {
                    let title = 'HOLIDAY';
                    if (holiday.is_half_day) {
                        title = 'HALF DAY HOLIDAY <span class="glyphicon glyphicon-adjust"></span>';
                    }
                    this.events.push({
                        'title': title,
                        'description': holiday.title,
                        'approved': true,
                        'holiday': holiday,
                    });
                }
            }

            for (let i in this.ptos) {
                let pto = this.ptos[i];
                if (
                    this.currentday.isSame(pto.start_time, 'day') ||
                    this.currentday.isSame(pto.end_time, 'day') ||
                    this.currentday.isBetween(pto.start_time, pto.end_time, 'day')
                ) {
                    this.events.push({
                        'title': pto.employee.name,
                        'description': pto.description,
                        'approved': pto.is_approved,
                        'pto': pto
                    });
                }
            }
        },
        isHalfDay(event) {
            if (event.holiday) {
                return;
            }
            if (event.pto.is_half_day) {
                return `<br><span class="glyphicon glyphicon-adjust"></span> Half Day `;
            }
        },
        isSentToCalendar(event) {
            if (event.holiday) {
                return;
            }
            if (event.pto.is_sent_to_calendar) {
                return `<br><span class="glyphicon glyphicon-calendar"></span> On OOO`;
            }
        },
        isApproved(approved) {
            if (approved) {
                return `<span class="glyphicon glyphicon-thumbs-up" style="color: green;" aria-hidden="true"></span> Approved`;
            }
            return `<span class="glyphicon glyphicon-thumbs-down" style="color: red;" aria-hidden="true"></span> Pending`;
        },
        reset() {
            this.events = [];
        }
    }
}
</script>
<style>
</style>