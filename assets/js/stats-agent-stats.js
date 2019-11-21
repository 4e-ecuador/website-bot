const $ = require('jquery')
const Highcharts = require('highcharts')

require('../css/stats-agent-stats.css')

function draw_chart(id) {
    let url = '/stats/agent/data/' + id
    $.getJSON(url,
        function (data) {
            let options = {
                chart: {
                    renderTo: 'container',
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
        }
    )
}

let id = $('#js-agent-id').data('js-agent-id')

draw_chart(id)

$('.medal-item').on('click', function (e) {
    const modal = $('#medalModal')
    const modalBody = modal.find('div.modal-body')

    modalBody.html($(this).find('span.medal-image').html())

    modal.find('h4.modal-title').html($(this).data('medal-name'))
    modal.find('div.modal-header-desc').html($(this).data('medal-desc'))
    modal.find('div.medal-value').html($(this).data('medal-value'))

    const level = $(this).data('medal-level')
    let i
    for (i = 1; i < 6; i++) {
        let img = '<img src="/build/images/badges/' + $(this).data('badge-name-' + i) + '" style="height: 24px">'
        if (i > level) {
            img = '<img src="/build/images/badges/' + $(this).data('badge-name-' + i) + '" style="height: 24px; opacity: 0.3;">'
        }

        modal.find('div.medal-value-' + i).html(img + $(this).data('medal-value-' + i))
    }
    modal.modal()
})
