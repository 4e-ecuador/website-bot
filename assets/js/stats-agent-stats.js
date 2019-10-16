const $ = require('jquery')
const Highcharts = require('highcharts')

function draw_chart(id) {
    let url = '/stats/agent/data/' + id
    $.getJSON(url,
        function (data) {
            let options = {
                chart: {
                    renderTo: 'container',
                    type: 'line'
                },
                title: {
                    text: ''
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                series: [
                    {
                        data: data.ap,
                        name: 'AP'
                    },
                    {
                        data: data.hacker,
                        name: 'Hacker'
                    }
                ]
            }

            let chart = new Highcharts.Chart(options)
        }
    )
}

let id = $('#js-agent-id').data('js-agent-id')

draw_chart(id)
