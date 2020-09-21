const $ = require('jquery')
const Highcharts = require('highcharts')

require('highcharts/css/themes/dark-unica.css')
require('../../../css/traditional/stats/agent-stats.css')

function draw_chart(start, end, container) {
    let id = $('#js-agent-id').data('js-agent-id')
    let dateStart = $('#'+start).val()
    let dateEnd = $('#'+end).val()

    let url = '/stats/agent/data/' + id + '/' + dateStart + '/' + dateEnd

    $.getJSON(url,
        function (data) {
            let options = {
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

            let series = chart.series;
            for(let i=1; i < chart.series.length; i++) {
                series[i].setVisible(false, false);
            }
            chart.redraw();
        }
    )
}

draw_chart('dateStart', 'dateEnd', 'agentChart')
draw_chart('dateStartAll', 'dateEndAll', 'agentChartAll')

$('.statsSelect').change(function (e) {
    draw_chart('dateStart', 'dateEnd', 'agentChart')
})

function updateModal(modal, e) {
    const modalBody = modal.find('div.modal-body')

    modalBody.html(e.find('span.medal-image').html())

    modal.find('h4.modal-title').html(e.data('medal-name'))
    modal.find('div.modal-header-desc').html(e.data('medal-desc'))
    modal.find('div.medal-value').html(e.data('medal-value'))

}

$('.medal-item').on('click', function (e) {
    const modal = $('#medalModal')
    updateModal(modal, $(this))
    const level = $(this).data('medal-level')
    let i
    for (i = 1; i < 6; i++) {
        let img = '<span class="medal24-badges medal-' + $(this).data('badge-name-' + i) + '"></span>'
        // if (i > level) {
        //     let img = '<span class="medal24 medal-'+$(this).data('badge-name-' + i)+'" style="background: #5C97FF;">a</span>'
        //     // img = '<img src="/build/images/badges/' + $(this).data('badge-name-' + i) + '" style="height: 24px; opacity: 0.3;">'
        // }

        modal.find('div.medal-value-' + i).html(img + $(this).data('medal-value-' + i))
    }
    modal.modal()
})

$('.medal-item2').on('click', function (e) {
    const modal = $('#medalModal2')
    updateModal(modal, $(this))
    modal.modal()
})
