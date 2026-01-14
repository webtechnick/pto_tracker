<template>
    <div id="calendar">
        <div class="ajax-loader">
            <bounce-loader :loading="loading" color="blue" size="175px"></bounce-loader>
        </div>

        <div class="calendar-grid">
            <table class="month" v-for="month in months" :key="month.num">
                <thead>
                    <tr><th colspan="7" class="month-title">{{ month.name }}</th></tr>
                    <tr class="weekday-header">
                        <th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(week, weekIndex) in month.weeks" :key="weekIndex">
                        <td class="day" v-bind:class="dayClass(month.num, day)" v-bind:id="dayId(month.num, day)" @click.prevent="selectDay(month.num, day, $event)" v-for="(day, dayIndex) in week" :key="dayIndex">
                            <a href="#" class="day-link" v-html="renderDay(month.num, day)" @click.prevent></a>
                        </td>
                    </tr>
                </tbody>
            </table>
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
            let html = `<span class="day-number">${day}</span>`;

            if (this.isWeekend(currentday)) {
                return html;
            }
            if (this.isFullDayHoliday(currentday)) {
                return html;
            }

            // Collect all PTOs for this day
            let dayPtos = [];
            for (let i in this.ptos) {
                let pto = this.ptos[i];
                if (currentday.isBetween(pto.start_time, pto.end_time, 'day', '[]')) {
                    dayPtos.push(pto);
                }
            }

            // Show up to 10, then "+N more" for overflow
            const maxVisible = 10; // Set to Infinity if want to disable. const maxVisible = Infinity;
            const visiblePtos = dayPtos.slice(0, maxVisible);
            const overflowCount = dayPtos.length - maxVisible;

            for (let pto of visiblePtos) {
                html += this.renderPto(pto);
            }

            if (overflowCount > 0) {
                const allNames = dayPtos.map(p => p.employee.name).join(', ');
                html += `<span class="pto-overflow" title="${allNames}">+${overflowCount}</span>`;
            }

            if (month == 12 && day == 31) {
                this.finishedLoading();
            }
            return html;
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
/* Loading spinner */
.ajax-loader {
    position: fixed;
    top: 35%;
    left: 35%;
    z-index: 9999;
}

/* CSS Grid layout for 4x3 month grid */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px 20px;
    align-items: start;
}

/* Month table */
.month {
    width: 100%;
    min-height: 280px;
    table-layout: fixed;
    border-collapse: collapse;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.month-title {
    text-align: center;
    font-size: 15px;
    font-weight: 600;
    padding: 10px 0;
    background: #f8f9fa;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
}

.weekday-header th {
    text-align: center;
    font-size: 11px;
    font-weight: 500;
    color: #868e96;
    padding: 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Day cells */
.day {
    cursor: pointer;
    border: 1px solid #e9ecef;
    vertical-align: top;
    padding: 4px;
    transition: background-color 0.15s ease;
    position: relative;
    overflow: visible;
}

.day:hover {
    background-color: #f8f9fa;
}

.day-link {
    display: flex;
    flex-wrap: wrap;
    align-content: flex-start;
    gap: 1px;
    color: #495057;
    text-decoration: none;
    font-size: 12px;
    line-height: 1.3;
    min-height: 55px;
}

.day-link:hover {
    text-decoration: none;
}

/* Day number */
.day-number {
    font-weight: 500;
    color: #495057;
    width: 100%;
    display: block;
    margin-bottom: 2px;
}

/* PTO indicators */
.pto-day {
    border-radius: 2px;
    font-size: 10px;
    font-weight: 600;
    line-height: 14px;
    display: inline-block;
    width: 14px;
    height: 14px;
    text-align: center;
}

.pending {
    opacity: 0.45;
}

/* Overflow indicator - full width footer button */
.pto-overflow {
    width: 100%;
    display: block;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    background: #e9ecef;
    border-radius: 3px;
    padding: 2px 0;
    margin-top: 2px;
    cursor: help;
    transition: background-color 0.15s ease;
}

.pto-overflow:hover {
    background: #dee2e6;
    color: #495057;
}

/* Special day states */
.holiday {
    background-color: #ffe0e6;
}

.half-holiday {
    background-color: #fff3cd;
}

.today {
    background-color: #d4edfc;
}

.selectedday {
    background-color: #d4edda;
}

/* Responsive: 2 columns on tablets */
@media (max-width: 992px) {
    .calendar-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Responsive: 1 column on mobile */
@media (max-width: 600px) {
    .calendar-grid {
        grid-template-columns: 1fr;
    }
}

.clear {
    clear: both;
}
</style>