jQuery(document).ready(function( $ ) {

    $(".colors > div").mouseover(function (e) {
        $(this).addClass("border-bottom-5-solid");
        $(this).siblings().removeClass("border-bottom-5-solid");

        $colorUrl = $(this).attr("id");
        $("#imgs > img").attr("src", $colorUrl);
    });

    $("#productColor").change(function (e) {
        $colorUrl = this.value;
        $("#imgs > img").attr("src", $colorUrl);
    });

});