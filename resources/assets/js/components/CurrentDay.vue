<template>
    <div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    Day: {{ currentday.format('l') }}
                </div>
                <div class="panel-body">
                    <ul>
                        <li v-for="event in events">
                            <h4 v-text="event.title"></h4>
                            <p v-text="event.description"></p>
                        </li>
                    </ul>
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
                return []
            }
        },
        holidays: {
            default () {
                return []
            }
        }
    },
    data() {
        return {
            'events': [

            ],
            'currentday': moment()
        };
    },
    mounted() {
        Events.$on('dayselect', this.rePopulateEvents.bind(this));
    },
    methods: {
        populateEvents() {
            this.rePopulateEvents(this.currentday.format('MM'), this.currentday.format('DD'));
        },
        rePopulateEvents(month, day) {
            console.log(month);
            console.log(day);
            console.log(this.holidays);
            this.currentday = moment(this.year + '-' + month + '-' + day, 'YYYY-MM-DD');
            this.reset();

            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (this.currentday.isSame(holiday.date, 'day')) {
                    this.events.push({
                        'title': 'HOLIDAY',
                        'description': holiday.title
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
                        'description': pto.description
                    });
                }
            }
        },
        reset() {
            this.events = [];
        }
    }
}
</script>
<style>
</style>