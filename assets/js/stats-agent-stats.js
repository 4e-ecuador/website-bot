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

$('.medal-item').on('click', function (e) {
    console.log(e)
    console.log(this)

    const modal = $('#medalModal')
    const modalBody = modal.find('div.modal-body')


    modalBody.html($(this).html())

    modal.find('h4.modal-title').html($(this).data('medal-name'))
    modal.find('div.modal-footer').html($(this).data('medal-desc'))
    modal.modal()
})
