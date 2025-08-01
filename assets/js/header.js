/**
 * Controlls header animations for the OSU MEng Header
 * 
 * This file assumes that Lodash is available.
 */
const $header = $('#header');
function onScroll() {
    let scrollPosition = Math.round(window.scrollY);
    if(scrollPosition > 0) {
        $header.removeClass('dark');
        $header.addClass('light');
        $('#navbarSmall').removeClass('navbar-dark');
        $('#navbarSmall').addClass('navbar-light');
    } else if (scrollPosition == 0) {
        $header.removeClass('light');
        $header.addClass('dark');
        $('#navbarSmall').removeClass('navbar-light');
        $('#navbarSmall').addClass('navbar-dark');
    }
}
window.addEventListener('scroll', _.throttle(onScroll, 100));