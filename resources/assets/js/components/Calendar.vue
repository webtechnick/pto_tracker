<template>
    <div>
        <div class="center"><h1>{{ this.year }}</h1></div>
        <div class="row">
            <div class="col-md-4" v-for="month in months">
                <table class="month">
                    <tr><td colspan='7' class="center">{{ month.name }}</td></tr>
                    <tr><th class="center">Su</th><th class="center">Mo</th><th class="center">Tu</th><th class="center">We</th><th class="center">Tr</th><th class="center">Fr</th><th class="center">Sa</th></tr>
                    <tr v-for="week in month.weeks">
                        <td class="center day" v-for="day in week">
                            <a href="#" v-html="renderDay(month.num, day)" @click.prevent="selectDay(month.num, day)"></a>
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
                return []
            }
        },
        holidays: {
            //type: Object,
            default () {
                return []
            }
        }
    },
    data() {
        return {
            'months': [
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
            ]
        };
    },
    mounted() {

    },
    methods: {
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
            day += ' ';
            let currentday = moment(this.year + '-' + month + '-' + day, 'YYYY-MM-DD');

            for (let i in this.ptos) {
                let pto = this.ptos[i];
                if (
                    currentday.isSame(pto.start_time, 'day') ||
                    currentday.isSame(pto.end_time, 'day') ||
                    currentday.isBetween(pto.start_time, pto.end_time, 'day')
                ) {
                    day += this.renderPto(pto);
                }
            }
            return day;
        },
        renderPto(pto) {
            let letter = pto.employee.name[0];
            return `<span class="day-pto" style="background-color:${pto.employee.bgcolor}; color: ${pto.employee.color};">${letter}</span>`;
        },
        selectDay(month, day) {
            Events.$emit('dayselect', month, day);
        },
        weekCount(month_number, year) {
            year = year || this.year;
            var firstOfMonth = new Date(year, month_number-1, 1);
            var lastOfMonth = new Date(year, month_number, 0);
            var used = firstOfMonth.getDay() + lastOfMonth.getDate();
            return Math.ceil( used / 7);
        },
        dateTimeText(pto) {
            let start = moment(pto.start_time).format('l');
            let end = moment(pto.end_time).format('l');
            return start + ' to ' + end;
        }
    }
}
</script>
<style>
.month {
    width: 100%;
    height: 350px;
}
.center {
    text-align: center;
}
.day-pto {
    border-radius: 2px;
    padding: 2px;
}
.day {
    border: 1px solid black;
    width: 40px;
    height: 50px;
}
</style>