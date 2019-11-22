const $ = require('jquery')

function getTimeRemaining(endtime){
    var t = Date.parse(endtime) - Date.parse(new Date());
    var seconds = Math.floor( (t/1000) % 60 );
    var minutes = Math.floor( (t/1000/60) % 60 );
    var hours = Math.floor( (t/(1000*60*60)) % 24 );
    var days = Math.floor( t/(1000*60*60*24) );
    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

function updateClock(){
    let t = getTimeRemaining(deadline);
    if(t.total<=0){
        clearInterval(timeinterval);
    }
    daysSpan.innerHTML = t.days;
    hoursSpan.innerHTML = t.hours;
    minutesSpan.innerHTML = t.minutes;
    secondsSpan.innerHTML = t.seconds;
    // clock.innerHTML = 'days: ' + t.days + '<br>' +
    //     'hours: '+ t.hours + '<br>' +
    //     'minutes: ' + t.minutes + '<br>' +
    //     'seconds: ' + t.seconds;
}


function initializeClock(){
    // var clock = document.getElementById(id);
    updateClock(); // run function once at first to avoid delay
    var timeinterval = setInterval(updateClock,1000);


    // var timeinterval = setInterval(function(){
    //     var t = getTimeRemaining(endtime);
    //     clock.innerHTML = 'days: ' + t.days + ' '
    //         + 'hours: '+ t.hours + ' '
    //         + 'minutes: ' + t.minutes + ' '
    //         + 'seconds: ' + t.seconds;
    //     if(t.total<=0){
    //         clearInterval(timeinterval);
    //     }
    // },1000);
}

let deadline = $('#js-data').data('deadline')

if (deadline) {

    var clock = document.getElementById('clockdiv');
    var daysSpan = clock.querySelector('.days');
    var hoursSpan = clock.querySelector('.hours');
    var minutesSpan = clock.querySelector('.minutes');
    var secondsSpan = clock.querySelector('.seconds');

    initializeClock();
}
