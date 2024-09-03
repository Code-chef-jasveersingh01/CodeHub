/*
File: dashboard
*/

document.addEventListener('DOMContentLoaded', function () {
    attendanceStatisticsChartPieFn(attendanceStatisticsChartPie, 0);
    departmentStatistics();
    latestAttendanceLogList();
    attendPerDepartmentList();
    absentPerDepartmentList();
});

// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        colors = JSON.parse(colors);
        return colors.map(function (value) {
            var newValue = value.replace(" ", "");
            if (newValue.indexOf(",") === -1) {
                var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                if (color) return color;
                else return newValue;;
            } else {
                var val = value.split(',');
                if (val.length == 2) {
                    var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                    rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                    return rgbaColor;
                } else {
                    return newValue;
                }
            }
        });
    }
}

// ----- start Attendance Statistics Chart Pie --------- //
var attendanceStatisticsChartPieColors = getChartColorsArray("attendance-statistics-chart-pie");
if (attendanceStatisticsChartPieColors) {
    var attendanceStatisticsChartPieDom = document.getElementById('attendance-statistics-chart-pie');
    var attendanceStatisticsChartPie = echarts.init(attendanceStatisticsChartPieDom);
    var option;

    option = {
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            textStyle: { //The style of the legend text
                color: '#858d98',
            },
        },
        color: attendanceStatisticsChartPieColors,
        series: [{
            // name: 'attendance Statistics',
            type: 'pie',
            radius: '50%',
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }],
        textStyle: {
            fontFamily: 'Poppins, sans-serif'
        },
    };

    option && attendanceStatisticsChartPie.setOption(option);
}

document.getElementById('attendance_statistics_filter').addEventListener("change", function (e) {
    e.preventDefault();
    attendanceStatisticsChartPieFn(attendanceStatisticsChartPie, document.getElementById("attendance_statistics_filter").value)
});

function attendanceStatisticsChartPieFn(attendanceStatisticsChartPie, days) {
    let base_url = window.location.origin;
    let paramsData = { day_count: days }

    attendanceStatisticsChartPie.showLoading();
    axios.get('' + base_url + '/attendance-statistics-pie-chart', { params: paramsData })
        .then(response => {
            attendanceStatisticsChartPie.setOption({
                series: [{
                    data: [
                        {
                            name: response.data.data[0].name,
                            value: response.data.data[0].value
                        },
                        {
                            name: response.data.data[1].name,
                            value: response.data.data[1].value
                        },
                        {
                            name: response.data.data[2].name,
                            value: response.data.data[2].value
                        }
                    ],
                }],
            });
            attendanceStatisticsChartPie.hideLoading();
        })
        .catch(error => {
            console.log('attendance statistics pie chart have problem');
        });
}
// ----- end Attendance Statistics Chart Pie --------- //


// ----- start department statistics --------- //
document.getElementById('department-statistics-filter').addEventListener("change", function (e) {
    e.preventDefault();
    departmentStatistics();
});

function departmentStatistics() {
    document.getElementById("department_statistics_table").classList.add("custom-loading");

    let base_url = window.location.origin;
    let days = document.getElementById("department-statistics-filter").value;

    axios.get('' + base_url + '/department-statistics-list/' + days + '')
        .then(response => {
            const tableBody = document.querySelector("#department-statistics-tbody");
            tableBody.innerHTML = '';

            const tableData = response.data.data.map(value => {
                return (
                    `<tr>
                        <td>${value.department_name}</td>
                        <td style="text-align: center;">${value.users_count}</td>
                        <td style="text-align: center;">${value.user_percentage}</td>
                        <td style="text-align: center;">${value.attendance_count}</td>
                        <td style="text-align: center;">${value.attendance_percentage}</td>
                    </tr>`
                );
            }).join('');

            tableBody.innerHTML = tableData;
            document.getElementById("department_statistics_table").classList.remove("custom-loading");
        })
        .catch(error => {
            document.getElementById("department_statistics_table").classList.remove("custom-loading");
            console.log('department statistics list have problem');
        });
}
// ----- end department statistics --------- //

// ----- start latest Attendance Log List --------- //
document.getElementById('refresh-attendance-log-list').addEventListener("click", function (e) {
    e.preventDefault();
    latestAttendanceLogList();
});

function latestAttendanceLogList() {
    document.getElementById("attendance-log-list-loader").classList.add("loader-ajax-small");
    let base_url = window.location.origin;

    axios.get('' + base_url + '/latest-attendance-log-list')
        .then(response => {
            const listBody = document.querySelector("#attendance-log-list");
            listBody.innerHTML = '';

            const tableData = response.data.data.map(value => {
                return (
                    `<li class="list-group-item d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="fs-14 mb-1">${value.full_name}</h6>
                            <p class="text-muted mb-0">${value.department_name}</p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge badge-soft-${value.type == 1 ? 'info' : 'secondary'}">${value.type_text}</span>
                            <p class="text-muted fs-12 mb-0">${value.log_time}</p>
                        </div>
                    </li>`
                );
            }).join('');

            listBody.innerHTML = tableData;
            document.getElementById("attendance-log-list-loader").classList.remove("loader-ajax-small");
        })
        .catch(error => {
            document.getElementById("attendance-log-list-loader").classList.remove("loader-ajax-small");
            console.log('latest Attendance Log List have problem');
        });
}
// ----- end latest Attendance Log List --------- //


// ----- start Top 10 Employee Attend per Department List --------- //
document.getElementById('refresh-attend-employee-list').addEventListener("click", function (e) {
    e.preventDefault();
    attendPerDepartmentList();
});

function attendPerDepartmentList() {
    document.getElementById("attend-employee-list-loader").classList.add("loader-ajax-small");
    let base_url = window.location.origin;

    axios.get('' + base_url + '/attend-per-department-list')
        .then(response => {
            const listBody = document.querySelector("#attend-employee-list");
            listBody.innerHTML = '';
            const tableData = response.data.data.map(value => {
                var usersHtml = '';
                if (value.users.length > 0) {
                    for (const element of value.users) {
                        usersHtml += `<span class="badge badge-soft-secondary">${element.full_name}</span> `;
                    }
                } else {
                    usersHtml += '-';
                }
                return (
                    `<li class="list-group-item d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="fs-13 mb-1">${value.department_name_lang}</span>
                        </div>
                        <div class="flex-shrink-0 text-end w-75">
                            ${usersHtml}
                        </div>
                    </li>`
                );
            }).join('');

            listBody.innerHTML = tableData;
            document.getElementById("attend-employee-list-loader").classList.remove("loader-ajax-small");
        })
        .catch(error => {
            document.getElementById("attend-employee-list-loader").classList.remove("loader-ajax-small");
            console.log('Top 10 Employee Attend per Department List have problem');
        });
}
// ----- end Top 10 Employee Attend per Department --------- //

// ----- start Top 10 User Absent per Department List --------- //
document.getElementById('refresh-absert-users-list').addEventListener("click", function (e) {
    e.preventDefault();
    absentPerDepartmentList();
});

function absentPerDepartmentList() {
    document.getElementById("absert-users-list-loader").classList.add("loader-ajax-small");
    let base_url = window.location.origin;

    axios.get('' + base_url + '/absent-per-department-list')
        .then(response => {
            const listBody = document.querySelector("#absert-users-list");
            listBody.innerHTML = '';
            const tableData = response.data.data.map(value => {
                var usersHtml = '';
                if (value.users.length > 0) {
                    for (const element of value.users) {
                        usersHtml += `<span class="badge badge-soft-secondary">${element.full_name}</span> `;
                    }
                } else {
                    usersHtml += '-';
                }
                return (
                    `<li class="list-group-item d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="fs-13 mb-1">${value.department_name_lang}</span>
                        </div>
                        <div class="flex-shrink-0 text-end w-75">
                            ${usersHtml}
                        </div>
                    </li>`
                );
            }).join('');

            listBody.innerHTML = tableData;
            document.getElementById("absert-users-list-loader").classList.remove("loader-ajax-small");
        })
        .catch(error => {
            document.getElementById("absert-users-list-loader").classList.remove("loader-ajax-small");
            console.log('Top 10 User Absent per Department List have problem');
        });
}
// ----- end Top 10 User Absent per Department --------- //
