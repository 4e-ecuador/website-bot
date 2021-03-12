require('../css/app.scss')
require('bootstrap')
require('bootstrap-select')

// start the Stimulus application
import '../bootstrap';

const $ = require('jquery');

let darkmode = localStorage.getItem('4e-darkmode')
checkDarkMode(darkmode)

$('#darkmode-toggle').click(function() {
    let darkmode = ('active' === localStorage.getItem('4e-darkmode')) ? 0 : 'active'
    localStorage.setItem("4e-darkmode", darkmode);
    checkDarkMode(darkmode)
});

function checkDarkMode(darkmode) {
    let body = $('body')
    body.removeClass('bootstrap-dark bootstrap')
    if ('active' === darkmode) {
        body.addClass('bootstrap-dark')
    } else {
        body.addClass('bootstrap')
    }
}
