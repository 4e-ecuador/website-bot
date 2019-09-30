const $ = require('jquery')

$('.pagLink').on('click', function () {
    goToPage($(this), $(this).attr('data-page'))
})

$('.paginatorReset').on('click', function () {
    resetAndSubmit($(this))
})

$('.paginatorResetChange').on('change', function () {
    resetAndSubmit($(this))
})

$('.paginatorCleanReset').on('click', function () {
    $(this).parent().prev().val('')
    resetAndSubmit($(this))
})

$('.paginatorOrder').on('click', function () {
    setOrdering($(this), $(this).attr('data-order'), $(this).attr('data-order-dir'))
})

function goToPage(e, page) {
    let form = e.closest('form')

    form.find('input[name="paginatorOptions[page]"]').val(page)

    form.submit()
}

function setOrdering(e, order, orderDir) {
    let form = e.closest('form')

    form.find('input[name="paginatorOptions[order]"]').val(order)
    form.find('input[name="paginatorOptions[orderDir]"]').val(orderDir)

    form.submit()
}

function resetAndSubmit(e) {
    let form = e.closest('form')

    form.find('input[name="paginatorOptions[page]"]').val('')
    form.find('input[name="paginatorOptions[order]"]').val('')
    form.find('input[name="paginatorOptions[orderDir]"]').val('')

    form.submit()
}
