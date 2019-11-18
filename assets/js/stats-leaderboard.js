const $ = require('jquery')

require('../css/stats-leaderboard.css')

$('.showAll').on('click', function () {
    $(this).next().slideToggle()
})
