const $ = require('jquery')

function getTimeRemaining(endtime){
    let t = Date.parse(endtime) - Date.parse(new Date())
    let seconds = Math.floor((t / 1000) % 60)
    let minutes = Math.floor((t / 1000 / 60) % 60)
    let hours = Math.floor((t / (1000 * 60 * 60)) % 24)
    let days = Math.floor(t / (1000 * 60 * 60 * 24))

    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

function updateClock(clock){
    let t = getTimeRemaining(clock.data('deadline'));
    // if(t.total<=0){
    //     clearInterval(timeinterval);
    // }
    clock.find('.days').html(t.days);
    clock.find('.hours').html(t.hours);
    clock.find('.minutes').html(t.minutes);
    clock.find('.seconds').html(t.seconds);
}

$('.clock-counter').each(function( index ) {
    updateClock($(this))
    setInterval(updateClock,1000, $(this))
});
