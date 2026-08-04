jQuery(document).ready(function( $ ) {

    $(".menu-nav > ul > li").hover(function (e) {
        $(this).children("a").toggleClass("active");
        $(this).children("ul").fadeToggle(150);
    });

    $("header > .inner > .more-info > a.hover-highlight").hover(function (e) {
        $(this).toggleClass("active");
    });

});