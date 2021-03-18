const $ = require('jquery')

require('../../../css/traditional/stats/leaderboard.css')

$('.showAll')
    .on('click', function () {
        const e = $(this)
        const old = e.html()
        e.html('<span class="spinner-border spinner-border-sm" role="status"></span>')
        $.post('/stats/leaderboard-detail', { item: e.data('item') })
            .done(function (data) {
                const modal = $('#detailsModal')
                modal.find('.modal-body')
                    .html(data)
                modal.modal()
                e.html(old)
            })
    })

$('.btnLoadBoard')
    .on('click', function () {
        const $input = $(this)
            .prev()

        let span = ''

        if ($input) {
            span = '/' + $input.attr('type') + ':' + $input.val()
        }

        window.location.href = '/stats/leaderboard' + span
    })
