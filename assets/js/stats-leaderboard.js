const $ = require('jquery')

require('../css/stats-leaderboard.css')

$('.showAll').on('click', function () {
    const e = $(this)
    const old = e.html()
    e.html('<span class="spinner-border spinner-border-sm" role="status"></span>')
    $.post('/stats/leaderboard-detail', {item: e.data('item')})
        .done(function (data) {
            const modal = $('#detailsModal')
            modal.find('.modal-body').html(data)
            modal.modal()
            e.html(old)
        })
})
