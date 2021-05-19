require('../css/app.css')
require('bootstrap')
require('bootstrap-select')

require('open-iconic/font/css/open-iconic-bootstrap.css')

require ("@forevolve/bootstrap-dark/dist/css/bootstrap-dark.css")
require('@forevolve/bootstrap-dark/dist/css/toggle-bootstrap.css')
require('@forevolve/bootstrap-dark/dist/css/toggle-bootstrap-dark.css')
require ("bootstrap-select/dist/css/bootstrap-select.css")

import "../css/medals_badges_24.css";
import "../css/medals_events_24.css";
import "../css/medals_badges_50.css";
import "../css/medals_events_50.css";

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
