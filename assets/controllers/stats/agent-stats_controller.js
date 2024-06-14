import { Controller } from '@hotwired/stimulus'

import Highcharts from 'highcharts'
require('highcharts/css/themes/dark-unica.css')

import '../../css/stats/agent-stats.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        userId: Number,
        dateStartAll: String,
        dateEndAll: String,
    }
    static targets = ['dateStart', 'dateEnd']

    async connect() {
        await this.redrawChart()
        await this._draw_chart(this.dateStartAllValue, this.dateEndAllValue, 'agentChartAll')
    }

    async redrawChart() {
        await this._draw_chart(this.dateStartTarget.value, this.dateEndTarget.value, 'agentChart')
    }

    async _draw_chart(dateStart, dateEnd, container) {
        const url = '/stats/agent/data/' + this.userIdValue + '/' + dateStart + '/' + dateEnd
        const response = await fetch(url)
        const data = await response.json()
        const options = {
            chart: {
                renderTo: container,
                type: 'line',
                dateFormat: 'YYYY/mm/dd',
                zoomType: 'x'

            },
            title: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    format: '{value:%b %Y}'
                }
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

        let series = chart.series
        for (let i = 1; i < chart.series.length; i++) {
            series[i].setVisible(false, false)
        }
        chart.redraw()
    }
}
