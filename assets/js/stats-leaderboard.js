const $ = require('jquery')

$('.showAll').on('click', function () {
    $(this).next().toggle()
})
