const $ = require('jquery')

$('.sendMail').click(function () {
    const userId = $(this).data('userid')

    const resultContainer = $('#mailResult')

    resultContainer.html('Sending email...')

    $.ajax({
        url: '/user/send-confirmation-mail/' + userId,
        // data: {
        //     id: agent
        // },

        success: function (result) {
            resultContainer.html(result)
        },
        error: function (result) {
            console.log(result)
            resultContainer.html('error: ' + result.statusText)

        }
    })
})
