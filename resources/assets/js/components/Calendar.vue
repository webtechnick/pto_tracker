<template>
    <div id="calendar">
        <div class="ajax-loader">
            <bounce-loader :loading="loading" color="blue" size="175px"></bounce-loader>
        </div>

        <div class="row">
            <div class="col-md-4" v-for="month in months">
                <table class="month">
                    <tr><td colspan='7' class="center">{{ month.name }}</td></tr>
                    <tr><th class="center">Su</th><th class="center">Mo</th><th class="center">Tu</th><th class="center">We</th><th class="center">Tr</th><th class="center">Fr</th><th class="center">Sa</th></tr>
                    <tr v-for="week in month.weeks">
                        <td class="center day" v-bind:class="dayClass(month.num, day)" v-bind:id="dayId(month.num, day)" @click.prevent="selectDay(month.num, day, $event)" v-for="day in week">
                            <a href="#" class="day-link" v-html="renderDay(month.num, day)" @click.prevent></a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'calendar',
    props: {
        year: {
            default() {
                return moment().format('YYYY');
            }
        },
        ptos: {
            //type: Array,
            default() {
                return [];
            }
        },
        holidays: {
            //type: Object,
            default () {
                return [1];
            }
        }
    },
    data() {
        return {
            'months': this.renderYear(),
            'loading': true,
            'today': moment()
        };
    },
    /*watch: {
        'ptos': function (val) {
            this.months = this.renderYear();
        }
    },*/
    mounted() {
        Events.$on('loading', this.startLoading.bind(this));
        Events.$on('finishedLoading', this.finishLoading.bind(this));
    },
    methods: {
        finishedLoading() {
            if (this.ptos.length) {
                this.loading = false;
            }
        },
        startLoading() {
            this.loading = true;
        },
        finishLoading() {
            this.loading = false;
        },
        renderYear() {
            return [
                {'name': 'Jan', 'num': 1, 'weeks': this.buildMonth('01')},
                {'name': 'Feb', 'num': 2, 'weeks': this.buildMonth('02')},
                {'name': 'Mar', 'num': 3, 'weeks': this.buildMonth('03')},
                {'name': 'Apr', 'num': 4, 'weeks': this.buildMonth('04')},
                {'name': 'May', 'num': 5, 'weeks': this.buildMonth('05')},
                {'name': 'Jun', 'num': 6, 'weeks': this.buildMonth('06')},
                {'name': 'Jul', 'num': 7, 'weeks': this.buildMonth('07')},
                {'name': 'Aug', 'num': 8, 'weeks': this.buildMonth('08')},
                {'name': 'Sep', 'num': 9, 'weeks': this.buildMonth('09')},
                {'name': 'Oct', 'num': 10, 'weeks': this.buildMonth('10')},
                {'name': 'Nov', 'num': 11, 'weeks': this.buildMonth('11')},
                {'name': 'Dec', 'num': 12, 'weeks': this.buildMonth('12')}
            ];
        },
        buildMonth(month_number) {
            let firstDay = new Date(this.year, month_number - 1, 1); // Feb 1st
            let startingDay = firstDay.getDay();
            let monthLength = moment(this.year + '-' + month_number, 'YYYY-MM').daysInMonth();
            let weeks = this.weekCount(month_number); // Feb
            let retval = [];

            let day = 1;
            for (let i = 0; i < weeks; i++) {
                retval[i] = [];
                for (let j = 0; j <= 6; j++) {
                    if (day <= monthLength && (i > 0 || j >= startingDay)) {
                        // Good option loading on watch of ptos, but it's fast to calculate anyway.
                        // Rather give the user something to look at than nothing.
                        //let cell = this.renderDay(month_number, day);
                        //retval[i].push(cell);
                        retval[i].push(day);
                        day++;
                    } else {
                        retval[i].push('');
                    }
                }
            }
            return retval;
        },
        renderDay(month, day) {
            if (day == '') {
                return '';
            }
            let currentday = this.getDay(month, day);
            if (this.isWeekend(currentday)) {
                return day;
            }
            if (this.isFullDayHoliday(currentday)) {
                return day;
            }
            day += ' ';
            let count = 0;

            for (let i in this.ptos) {
                let pto = this.ptos[i];
                if (currentday.isBetween(pto.start_time, pto.end_time, 'day', '[]')) {
                    if (count % 3 === 0) {
                        day += ' ';
                    }
                    day += this.renderPto(pto);
                    count++;
                }
            }
            if (month == 12 && day == 31) {
                this.finishedLoading();
            }
            return day;
        },
        isHoliday(moment_day) {
            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (moment_day.isSame(holiday.date, 'day')) {
                    return true;
                }
            }
            return false;
        },
        isFullDayHoliday(moment_day) {
            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (!holiday.is_half_day && moment_day.isSame(holiday.date, 'day')) {
                    return true;
                }
            }
            return false;
        },
        isHalfDayHoliday(moment_day) {
            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (holiday.is_half_day && moment_day.isSame(holiday.date, 'day')) {
                    return true;
                }
            }
            return false;
        },
        isWeekend(moment_day) {
            let weekday = moment_day.weekday();
            return (weekday == 0 || weekday == 6);
        },
        renderPto(pto) {
            let letter = pto.employee.name[0];
            let style = 'pto-day';
            if (!pto.is_approved) {
                style += ' pending';
            }
            return `<span class="${style}" style="background-color:${pto.employee.bgcolor}; color: ${pto.employee.color};">${letter}</span>`;
        },
        selectDay(month, day, event) {
            if (day == '') {
                return;
            }
            $('#calendar .day').removeClass('selectedday');
            let daycell = $('#' + month + '-' + day);
            if (daycell) {
                daycell.addClass('selectedday');
            }
            Events.$emit('dayselect', month, day);
        },
        weekCount(month_number, year) {
            year = year || this.year;
            var firstOfMonth = new Date(year, month_number-1, 1);
            var lastOfMonth = new Date(year, month_number, 0);
            var used = firstOfMonth.getDay() + lastOfMonth.getDate();
            return Math.ceil( used / 7);
        },
        dayClass(month, day) {
            if (day == '') {
                return '';
            }
            let currentday = this.getDay(month, day);
            if (currentday.isSame(this.today, 'day')) {
                return 'today';
            }
            for (let i in this.holidays) {
                let holiday = this.holidays[i];
                if (currentday.isSame(holiday.date, 'day')) {
                    if (holiday.is_half_day) {
                        return 'half-holiday';
                    }
                    return 'holiday';
                }
            }
            return '';
        },
        dayId(month, day) {
            if (day == '') {
                return '';
            }
            return month + '-' + day;
        },
        getDay(month, day) {
            return moment(this.year + '-' + month + '-' + day, 'YYYY-MM-DD');
        }
    }
}
</script>
<style>
.ajax-loader {
    position: fixed;
    top: 35%;
    left: 35%;
    z-index: 9999;
}
.month {
    width: 100%;
    height: 400px;
}
.center {
    text-align: center;
}
.pto-day {
    border-radius: 3px;
    padding: 1px;
}
.holiday {
    background-color: lightpink;
}
.half-holiday {
    background-color: blanchedalmond;
}
.today {
    background-color: lightblue;
}
.selectedday {
    background-color: lightgreen;
}
.pending {
    opacity: 0.4;
    filter: alpha(opacity=40); /* For IE8 and earlier */
}
.day-link {
    color: grey;
    word-wrap: break-word;
}
.day {
    cursor: pointer;
    border: 1px solid black;
    width: 43px;
    height: 50px;
}
.clear {
    clear: both;
}
</style>